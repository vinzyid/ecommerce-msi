<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(private readonly ChatbotService $chatbot)
    {
    }

    /**
     * Terima pertanyaan dari widget chatbot dan kembalikan jawaban.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
        ]);

        try {
            $result = $this->chatbot->ask(
                $validated['question'],
                $request->user()?->id,
                $validated['history'] ?? []
            );

            return response()->json([
                'answer' => $result['answer'],
                'cached' => $result['cached'],
                'blocked' => $result['blocked'] ?? false,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Maaf, layanan chatbot sedang bermasalah. Coba lagi sebentar ya.',
            ], 500);
        }
    }
}
