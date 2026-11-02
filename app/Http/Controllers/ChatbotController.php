<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFeedback;
use App\Models\Order;
use App\Models\Product;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChatbotController extends Controller
{
    private const GENERIC_ERROR = 'Maaf, layanan chatbot sedang bermasalah. Coba lagi sebentar ya.';

    public function __construct(private readonly ChatbotService $chatbot)
    {
    }

    /**
     * Terima pertanyaan dari widget chatbot dan kembalikan jawaban.
     *
     * Dengan header "Accept: application/x-ndjson" jawaban dikirim bertahap,
     * satu objek JSON per baris: {"type":"delta"}, lalu {"type":"done"} atau {"type":"error"}.
     */
    public function ask(Request $request): JsonResponse|StreamedResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
            'product' => ['nullable', 'string', 'max:150'],
        ]);

        $pageProduct = filled($validated['product'] ?? null)
            ? Product::query()->where('slug', $validated['product'])->where('is_active', true)->first()
            : null;

        $arguments = [
            $validated['question'],
            $request->user()?->id,
            $validated['history'] ?? [],
            $pageProduct,
        ];

        if (str_contains((string) $request->header('Accept'), 'application/x-ndjson')) {
            return $this->stream($arguments);
        }

        try {
            return response()->json($this->payload($this->chatbot->ask(...$arguments)));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => self::GENERIC_ERROR], 500);
        }
    }

    /**
     * Simpan penilaian 👍/👎 untuk satu jawaban chatbot.
     */
    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'in:1,-1'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'message_id' => ['nullable', 'integer', 'exists:chatbot_messages,id'],
        ]);

        ChatbotFeedback::query()->create([
            'user_id' => $request->user()?->id,
            'chatbot_message_id' => $validated['message_id'] ?? null,
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'rating' => (int) $validated['rating'],
        ]);

        return response()->json(['ok' => true], 201);
    }

    private function stream(array $arguments): StreamedResponse
    {
        return response()->stream(function () use ($arguments) {
            $emit = function (array $event): void {
                echo json_encode($event, JSON_UNESCAPED_UNICODE)."\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $result = $this->chatbot->ask(
                    ...$arguments,
                    onDelta: fn (string $text) => $emit(['type' => 'delta', 'text' => $text]),
                );

                $emit(['type' => 'done'] + $this->payload($result));
            } catch (RuntimeException $e) {
                $emit(['type' => 'error', 'error' => $e->getMessage()]);
            } catch (Throwable $e) {
                report($e);
                $emit(['type' => 'error', 'error' => self::GENERIC_ERROR]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function payload(array $result): array
    {
        return [
            'answer' => $result['answer'],
            'cached' => $result['cached'],
            'blocked' => $result['blocked'],
            'message_id' => $result['message_id'],
            'products' => $this->productCards($result['products']),
            'orders' => $result['orders']->map(fn (Order $order) => [
                'number' => $order->order_number,
                'status' => $order->statusLabel(),
                'url' => route('orders.show', $order),
            ])->all(),
        ];
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function productCards(Collection $products): array
    {
        $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');

        return $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'url' => route('products.show', $product),
            'image_url' => $product->image_url,
            'price' => $rupiah($product->price),
            'compare_at_price' => $product->hasDiscount() ? $rupiah($product->compare_at_price) : null,
            'discount_percent' => $product->hasDiscount() ? $product->discountPercent() : null,
            'in_stock' => $product->stock > 0,
        ])->all();
    }
}
