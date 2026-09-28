<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Pilih nilai rating.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'comment.max' => 'Ulasan maksimal 500 karakter.',
        ]);

        Review::query()->updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $request->user()->id],
            $validated + ['is_approved' => true]
        );

        return back()->with('success', 'Terima kasih, ulasan Anda tersimpan.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id || $request->user()->is_admin, 403);

        $review->delete();

        return back()->with('success', 'Ulasan dihapus.');
    }
}
