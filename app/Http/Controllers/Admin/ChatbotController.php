<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) ($request->validate(['q' => 'nullable|string|max:100'])['q'] ?? ''));

        $messages = ChatbotMessage::query()
            ->with('user')
            ->when($search, fn ($query, $search) => $query->where('question', 'like', "%{$search}%"))
            ->orderByDesc('hit_count')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = $this->stats();

        return view('admin.chatbot.index', compact('messages', 'stats', 'search'));
    }

    public function destroy(ChatbotMessage $chatbotMessage): RedirectResponse
    {
        $chatbotMessage->delete();

        return back()->with('success', 'Entri cache chatbot dihapus.');
    }

    public function purge(): RedirectResponse
    {
        $deleted = ChatbotMessage::query()->delete();

        return back()->with('success', "Semua cache chatbot ({$deleted} entri) dibersihkan.");
    }

    /**
     * Statistik GREEN COMPUTING: berapa token, energi listrik, dan emisi karbon yang dihemat
     * lewat cache pertanyaan (termasuk pertanyaan yang mirip).
     *
     * Estimasi: rata-rata 1 panggilan AI memakai ~600 token output + prompt
     * produk (~2.500 token). Setiap "hit" menghemat ~3.100 token.
     */
    private function stats(): array
    {
        $totalEntries = ChatbotMessage::query()->count();
        $totalHits = (int) ChatbotMessage::query()->sum('hit_count');
        $freshEntries = ChatbotMessage::query()->where('expires_at', '>', now())->count();
        $staleEntries = $totalEntries - $freshEntries;
        $totalAiCalls = $totalEntries; // setiap entri = 1 panggilan AI asli

        $tokensPerCall = 3100;
        $tokensSaved = $totalHits * $tokensPerCall;

        // Estimasi energi: ~0,001 kWh per 1.000 token (kasar, referensi GPU inference).
        $kwhSaved = round($tokensSaved / 1000 * 0.001, 4);

        // Estimasi emisi karbon: ~0,8 kg CO2e per kWh (grid listrik rata-rata).
        $co2SavedGrams = round($kwhSaved * 800, 2);

        return [
            'total_entries' => $totalEntries,
            'total_hits' => $totalHits,
            'fresh_entries' => $freshEntries,
            'stale_entries' => $staleEntries,
            'ai_calls' => $totalAiCalls,
            'tokens_saved' => $tokensSaved,
            'kwh_saved' => $kwhSaved,
            'co2_saved_grams' => $co2SavedGrams,
            'cache_ttl' => (int) config('services.chatbot.cache_ttl', 60),
            'model' => (string) config('services.chatbot.model'),
            'hit_rate' => ($totalEntries + $totalHits) > 0
                ? round($totalHits / ($totalEntries + $totalHits) * 100, 1)
                : 0.0,
        ];
    }
}
