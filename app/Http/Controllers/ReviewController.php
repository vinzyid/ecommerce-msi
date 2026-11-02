<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'rating.required' => 'Pilih nilai rating.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'comment.max' => 'Ulasan maksimal 500 karakter.',
        ]);

        $user = $request->user();

        if (! $user->canReview($product)) {
            return back()->withErrors([
                'review' => 'Ulasan bisa ditulis setelah pesanan selesai.',
            ]);
        }

        unset($validated['photo']);
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = Review::storePhoto($request->file('photo'));
        }

        Review::query()->updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $user->id],
            $validated + ['is_approved' => true]
        );

        return back()->with('success', 'Terima kasih, ulasan Anda tersimpan.');
    }

    /**
     * Simpan ulasan beberapa barang sekaligus dari riwayat atau detail pesanan.
     */
    public function storeForOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== 'completed') {
            return back()->withErrors(['review' => 'Ulasan bisa ditulis setelah pesanan selesai.']);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.rating' => 'nullable|integer|min:1|max:5',
            'items.*.comment' => 'nullable|string|max:500',
            'items.*.photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'items.*.photo.image' => 'File harus berupa gambar.',
            'items.*.photo.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'items.*.photo.max' => 'Ukuran foto maksimal 2 MB.',
            'items.required' => 'Pilih bintang untuk minimal satu barang.',
            'items.*.comment.max' => 'Ulasan maksimal 500 karakter.',
        ]);

        // Hanya barang yang ada di pesanan ini yang diproses, apa pun isi form yang dikirim.
        $saved = 0;
        foreach ($order->items()->whereNotNull('product_id')->pluck('product_id')->unique() as $productId) {
            $rating = $validated['items'][$productId]['rating'] ?? null;

            if ($rating === null) {
                continue;
            }

            $attributes = [
                'rating' => (int) $rating,
                'comment' => $validated['items'][$productId]['comment'] ?? null,
                'is_approved' => true,
            ];

            if ($request->hasFile("items.{$productId}.photo")) {
                $attributes['photo_path'] = Review::storePhoto($request->file("items.{$productId}.photo"));
            }

            Review::query()->updateOrCreate(['product_id' => $productId, 'user_id' => $request->user()->id], $attributes);
            $saved++;
        }

        if ($saved === 0) {
            return back()->withErrors(['review' => 'Pilih bintang untuk minimal satu barang.']);
        }

        return back()->with('success', 'Terima kasih, penilaianmu tersimpan.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id || $request->user()->is_admin, 403);

        $review->delete();

        return back()->with('success', 'Ulasan dihapus.');
    }
}
