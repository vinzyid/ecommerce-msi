<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
        ]);

        $products = $this->productQuery()
            ->when($filters['q'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $slug) {
                $query->whereHas('category', fn (Builder $query) => $query->where('slug', $slug));
            })
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $featuredProducts = $this->productQuery()
            ->where('is_featured', true)
            ->orderBy('name')
            ->limit(4)
            ->get();

        $heroProduct = $featuredProducts->first();
        $wishlistedProductIds = $this->wishlistedIds($products->getCollection()->concat($featuredProducts));

        return view('catalog.index', compact(
            'products',
            'categories',
            'featuredProducts',
            'heroProduct',
            'wishlistedProductIds',
        ));
    }

    private function productQuery(): Builder
    {
        return Product::query()
            ->with('category')
            ->withCount(['reviews' => fn (Builder $query) => $query->where('is_approved', true)])
            ->withAvg(['reviews' => fn (Builder $query) => $query->where('is_approved', true)], 'rating')
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true));
    }

    private function wishlistedIds(\Illuminate\Support\Collection $products): array
    {
        if (! auth()->check()) {
            return [];
        }

        $ids = $products->pluck('id')->unique();

        return auth()->user()->wishlists()->whereIn('product_id', $ids)->pluck('product_id')->all();
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active && $product->category->is_active, 404);

        $product->load(['reviews' => fn ($query) => $query->where('is_approved', true)->with('user')->latest()]);

        $relatedProducts = $this->productQuery()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get();

        $wishlistedProductIds = $this->wishlistedIds($relatedProducts);

        $ratingBreakdown = collect(range(5, 1))->mapWithKeys(fn (int $star) => [
            $star => $product->reviews->where('rating', $star)->count(),
        ]);

        $isWishlisted = auth()->check()
            && auth()->user()->wishlists()->where('product_id', $product->id)->exists();

        $userReview = auth()->check()
            ? $product->reviews->firstWhere('user_id', auth()->id())
            : null;

        return view('catalog.show', compact(
            'product',
            'relatedProducts',
            'ratingBreakdown',
            'isWishlisted',
            'userReview',
            'wishlistedProductIds',
        ));
    }

    public function categories(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('catalog.categories', compact('categories'));
    }

    public function promo(Request $request): View
    {
        $products = $this->productQuery()
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->orderByRaw('(compare_at_price - price) DESC')
            ->paginate(12);

        $wishlistedProductIds = $this->wishlistedIds($products->getCollection());

        return view('catalog.promo', compact('products', 'wishlistedProductIds'));
    }
}
