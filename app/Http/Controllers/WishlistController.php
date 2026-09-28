<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $wishlists = $request->user()->wishlists()
            ->with([
                'product.category',
                'product' => fn ($query) => $query
                    ->withCount(['reviews' => fn ($query) => $query->where('is_approved', true)])
                    ->withAvg(['reviews' => fn ($query) => $query->where('is_approved', true)], 'rating'),
            ])
            ->latest()
            ->paginate(12);

        return view('wishlist.index', compact('wishlists'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlists()->firstOrCreate(['product_id' => $product->id]);

        return back()->with('success', "{$product->name} ditambahkan ke wishlist.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlists()->where('product_id', $product->id)->delete();

        return back()->with('success', "{$product->name} dihapus dari wishlist.");
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->wishlists()->delete();

        return back()->with('success', 'Wishlist dikosongkan.');
    }
}
