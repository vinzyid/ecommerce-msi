<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'user_id',
        'question',
        'question_hash',
        'canonical_key',
        'answer',
        'from_ai',
        'hit_count',
        'expires_at',
    ];

    /**
     * Sinonim / alias istilah toko -> bentuk baku.
     * Agar "ps5", "playstation5", "play station 5" dianggap sama.
     */
    private const SYNONYMS = [
        'ps5' => 'playstation 5',
        'ps4' => 'playstation 4',
        'playstation5' => 'playstation 5',
        'playstation4' => 'playstation 4',
        'harganya' => 'harga',
        'brp' => 'harga',
        'berapaan' => 'harga',
        'stoknya' => 'stok',
        'stock' => 'stok',
        'ready' => 'stok',
        'tersedia' => 'stok',
        'promonya' => 'promo',
        'diskon' => 'promo',
        'diskonan' => 'promo',
        'potongan' => 'promo',
        'promosi' => 'promo',
        'rekomen' => 'rekomendasi',
        'rekomendasiin' => 'rekomendasi',
        'saran' => 'rekomendasi',
        'headphone' => 'headset',
        'earphone' => 'headset',
        'keybord' => 'keyboard',
        'mousepad' => 'mouse',
        'miniatur' => 'diecast',
        'hotwheels' => 'diecast',
        'diecast' => 'diecast',
        'minigt' => 'diecast',
        'gpu' => 'vga',
        'kartu grafis' => 'vga',
        'ram' => 'ram',
        'konsol' => 'konsol',
        'console' => 'konsol',
        'kirim' => 'pengiriman',
        'ongkir' => 'pengiriman',
        'ongkos' => 'pengiriman',
        'kurir' => 'pengiriman',
        'bayar' => 'pembayaran',
        'beli' => 'beli',
    ];

    /**
     * Kata basa-basi yang dibuang sebelum mencocokkan.
     */
    private const STOPWORDS = [
        'halo', 'hai', 'hello', 'hi', 'min', 'admin', 'kak', 'gan', 'bos', 'bang',
        'tolong', 'mohon', 'bisa', 'bisakah', 'apakah', 'apa', 'dong', 'ya', 'sih',
        'deh', 'yang', 'ada', 'mau', 'tanya', 'nih', 'yaa', 'aku', 'saya', 'kamu',
        'ini', 'itu', 'nya', 'kah', 'lah', 'pun', 'ke', 'di', 'dan', 'atau', 'untuk',
        'sama', 'juga', 'saja', 'aja', 'gak', 'tidak', 'engga', 'ngga', 'bgt',
        'banget', 'kalo', 'kalau', 'dengan', 'pada', 'dari', 'itu',
        'berapa', 'brp', 'brapa', 'berapakah', 'berapaannya',
    ];

    protected function casts(): array
    {
        return [
            'from_ai' => 'boolean',
            'hit_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normalisasi pertanyaan agar variasi huruf besar/kecil, tanda baca,
     * dan spasi berlebih tetap dianggap pertanyaan yang sama.
     */
    public static function normalizeQuestion(string $question): string
    {
        $normalized = Str::lower(trim($question));

        // Buang tanda baca, sisakan huruf/angka/spasi.
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized);

        // Rapikan spasi berlebih.
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    /**
     * Kunci kanonik: pertanyaan mirip menghasilkan kunci yang sama sehingga
     * bisa dihitung sebagai cache-hit (green computing) tanpa memanggil AI lagi.
     */
    public static function canonicalKey(string $question): string
    {
        $text = self::normalizeQuestion($question);

        // Ganti sinonim istilah toko (multi-kata dulu).
        foreach (self::SYNONYMS as $from => $to) {
            if (str_contains($from, ' ')) {
                $text = str_replace($from, $to, $text);
            }
        }

        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = array_map(function (string $token) {
            return self::SYNONYMS[$token] ?? $token;
        }, $tokens);

        // Buang stopword (pertahankan angka walau 1 digit, mis. "5" pada "ps5").
        $tokens = array_values(array_filter(
            $tokens,
            function (string $token) {
                if (in_array($token, self::STOPWORDS, true)) {
                    return false;
                }
                if (ctype_digit($token)) {
                    return true;
                }

                return mb_strlen($token) > 1;
            },
        ));

        // Ganti sinonim yang berupa frasa (setelah tokenisasi).
        $tokens = array_map(fn (string $t) => self::SYNONYMS[$t] ?? $t, $tokens);

        // Urutkan agar urutan kata tidak berpengaruh.
        sort($tokens);

        return implode(' ', array_unique($tokens));
    }

    /**
     * Skor kemiripan (0-100) antara pertanyaan ini dengan teks lain
     * memakai kombinasi token overlap dan similar_text.
     */
    public function similarityTo(string $otherQuestion): float
    {
        $a = self::tokenSet($this->canonical_key ?: self::canonicalKey($this->question));
        $b = self::tokenSet(self::canonicalKey($otherQuestion));

        if (empty($a) || empty($b)) {
            return 0.0;
        }

        // Hitung token yang sama (irisan)
        $intersect = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));
        $jaccard = $union > 0 ? $intersect / $union : 0.0;

        // Containment ratio: seberapa banyak kata kunci dari pertanyaan pendek yang ada di pertanyaan panjang
        $minLen = min(count($a), count($b));
        $containment = $minLen > 0 ? $intersect / $minLen : 0.0;

        // Bobot: 50% Jaccard + 50% Containment (fokus pada kesamaan kata kunci produk & maksud).
        $overlapScore = ($jaccard * 0.5 + $containment * 0.5) * 100;

        return $overlapScore;
    }

    /**
     * @return array<int, string>
     */
    private static function tokenSet(?string $canonical): array
    {
        $tokens = preg_split('/\s+/', (string) $canonical, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique($tokens));
    }

    /**
     * Hash normalisasi pertanyaan — dipakai sebagai kunci unik cache.
     */
    public static function hashQuestion(string $question): string
    {
        return hash('sha256', self::normalizeQuestion($question));
    }

    /**
     * Apakah jawaban ini masih berlaku (belum kedaluwarsa)?
     */
    public function isFresh(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isFuture();
    }
}
