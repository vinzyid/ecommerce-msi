<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ChatbotBenchmark extends Command
{
    protected $signature = 'chatbot:benchmark
                            {--models= : Daftar model dipisah koma (default: dari .env / daftar contoh)}
                            {--question= : Pertanyaan tunggal untuk diuji}
                            {--file= : Path file berisi daftar pertanyaan (satu per baris)}
                            {--with-catalog : Sertakan konteks data produk toko (seperti chatbot asli)}
                            {--time : Tampilkan waktu respons tiap model}';

    protected $description = 'Bandingkan kualitas & kecepatan beberapa model AI chatbot (OpenAI-compatible)';

    public function handle(): int
    {
        $apiKey = config('services.chatbot.api_key');
        $baseUrl = rtrim((string) config('services.chatbot.base_url'), '/');

        if (empty($apiKey)) {
            $this->error('CHATBOT_API_KEY belum diatur di .env');
            return self::FAILURE;
        }

        $models = $this->resolveModels();
        $questions = $this->resolveQuestions();

        if (empty($models) || empty($questions)) {
            $this->error('Model atau pertanyaan kosong.');
            return self::FAILURE;
        }

        $this->info('Endpoint : '.$baseUrl);
        $this->info('Model    : '.implode(', ', $models));
        $this->info('Konteks produk: '.($this->option('with-catalog') ? 'YA (seperti chatbot asli)' : 'tidak'));
        $this->newLine();

        $results = [];

        foreach ($models as $model) {
            $this->line("▶ Menguji: <fg=cyan>{$model}</>");
            $bar = $this->output->createProgressBar(count($questions));
            $bar->start();

            foreach ($questions as $question) {
                $start = microtime(true);
                try {
                    $response = Http::withToken($apiKey)
                        ->timeout((int) config('services.chatbot.timeout', 60))
                        ->acceptJson()
                        ->post("{$baseUrl}/chat/completions", [
                            'model' => $model,
                            'temperature' => (float) config('services.chatbot.temperature', 0.3),
                            'max_tokens' => (int) config('services.chatbot.max_tokens', 600),
                            'messages' => [
                                ['role' => 'system', 'content' => $this->systemPrompt()],
                                ['role' => 'user', 'content' => $question],
                            ],
                        ]);

                    $elapsed = microtime(true) - $start;
                    $body = $response->json();

                    if ($response->failed()) {
                        $results[$model][] = [
                            'question' => $question,
                            'answer' => '[ERROR HTTP '.$response->status().'] '.substr((string) $response->body(), 0, 200),
                            'time' => $elapsed,
                            'tokens' => null,
                        ];
                    } else {
                        $results[$model][] = [
                            'question' => $question,
                            'answer' => (string) data_get($body, 'choices.0.message.content', '(kosong)'),
                            'time' => $elapsed,
                            'tokens' => data_get($body, 'usage.total_tokens'),
                            'model_used' => data_get($body, 'model'),
                        ];
                    }
                } catch (\Throwable $e) {
                    $results[$model][] = [
                        'question' => $question,
                        'answer' => '[EXCEPTION] '.$e->getMessage(),
                        'time' => microtime(true) - $start,
                        'tokens' => null,
                    ];
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
        }

        $this->renderResults($results, $questions);

        return self::SUCCESS;
    }

    private function resolveModels(): array
    {
        $opt = $this->option('models');

        if ($opt) {
            return array_values(array_filter(array_map('trim', explode(',', $opt))));
        }

        // Ambil dari .env, buang yang kosong.
        $fromEnv = array_filter(array_map('trim', explode(',', (string) config('services.chatbot.model'))));
        if (! empty($fromEnv)) {
            return array_values($fromEnv);
        }

        return ['deepseek-v4.1-flash', 'gemini-3.8-flash'];
    }

    private function resolveQuestions(): array
    {
        if ($q = $this->option('question')) {
            return [$q];
        }

        if ($file = $this->option('file')) {
            if (! is_file($file)) {
                $this->error("File tidak ditemukan: {$file}");
                return [];
            }
            return array_values(array_filter(array_map('trim', file($file))));
        }

        return [
            'Berapa harga PlayStation 5 dan stoknya masih ada?',
            'Rekomendasi headset gaming di bawah Rp2.000.000 yang stoknya tersedia.',
            'Siapa presiden Indonesia yang menjabat saat ini, dan apa yang kamu ketahui tentang data terbaru hingga kapan?',
        ];
    }

    private function systemPrompt(): string
    {
        $storeName = config('app.name', 'Toko');

        $base = <<<PROMPT
        Kamu adalah asisten virtual toko "{$storeName}", toko yang menjual produk gaming, diecast, dan hobi.
        Jawab ramah, singkat, akurat dalam Bahasa Indonesia. Harga dalam Rupiah.
        Jika ditanya soal pengetahuan umum / data terbaru, jawab jujur dan sebutkan batas pengetahuanmu sampai kapan.
        PROMPT;

        if (! $this->option('with-catalog')) {
            return $base;
        }

        $catalog = app(\App\Services\ChatbotService::class)->catalogContext();

        return $base."\n\nATURAN: Jawab harga/stok/produk HANYA dari DATA PRODUK di bawah. Jangan mengarang.\n\n"
            ."DATA PRODUK:\n{$catalog}";
    }

    private function renderResults(array $results, array $questions): void
    {
        foreach ($results as $model => $rows) {
            $this->newLine();
            $this->line(str_repeat('=', 70));
            $this->line("  MODEL: <fg=cyan>{$model}</>");
            $this->line(str_repeat('=', 70));

            foreach ($rows as $i => $row) {
                $this->newLine();
                $this->line('<fg=yellow>Pertanyaan ' . ($i + 1) . ':</> ' . $row['question']);
                $this->line('<fg=green>Jawaban:</>');
                $this->line(wordwrap($row['answer'], 74, "\n", false));

                if ($this->option('time')) {
                    $meta = sprintf('  ⏱  %.2f dtk', $row['time']);
                    if (! empty($row['tokens'])) {
                        $meta .= " | tokens: {$row['tokens']}";
                    }
                    if (! empty($row['model_used']) && $row['model_used'] !== $model) {
                        $meta .= " | model asli: {$row['model_used']}";
                    }
                    $this->line('<fg=gray>'.$meta.'</>');
                }
            }
        }

        // Ringkasan rata-rata waktu
        if ($this->option('time')) {
            $this->newLine();
            $this->line(str_repeat('-', 70));
            $this->line('  RINGKASAN KECEPATAN');
            $this->table(
                ['Model', 'Rata-rata (dtk)', 'Total tokens'],
                collect($results)->map(function ($rows, $model) {
                    $times = array_column($rows, 'time');
                    $tokens = array_sum(array_filter(array_column($rows, 'tokens')));
                    return [$model, number_format(array_sum($times) / count($times), 2), $tokens ?: '-'];
                })->values()->all()
            );
        }

        $this->newLine();
        $this->info('Selesai. Salin jawaban terbaik ke CHATBOT_MODEL di .env');
    }
}
