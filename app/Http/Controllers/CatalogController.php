<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Voucher;
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
            'sort' => 'nullable|string|in:featured,latest,price_asc,price_desc',
        ]);

        $sort = $filters['sort'] ?? 'featured';

        $products = $this->productQuery()
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->when($filters['category'] ?? null, function (Builder $query, string $slug) {
                $query->whereHas('category', fn (Builder $query) => $query->where('slug', $slug));
            })
            ->when($sort === 'latest', fn ($q) => $q->latest('id'))
            ->when($sort === 'price_asc', fn ($q) => $q->orderBy('price', 'asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
            ->when($sort === 'featured', fn ($q) => $q->orderByDesc('is_featured')->orderBy('name'))
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

        $flashSaleProducts = $this->productQuery()
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->orderByRaw('(compare_at_price - price) DESC')
            ->limit(6)
            ->get();

        $vouchers = Voucher::query()
            ->where('is_active', true)
            ->orderBy('min_spend')
            ->limit(4)
            ->get();

        $heroProduct = $featuredProducts->first();
        $wishlistedProductIds = $this->wishlistedIds(
            $products->getCollection()
                ->concat($featuredProducts)
                ->concat($flashSaleProducts)
        );

        return view('catalog.index', compact(
            'products',
            'categories',
            'featuredProducts',
            'flashSaleProducts',
            'vouchers',
            'heroProduct',
            'wishlistedProductIds',
        ));
    }

    /**
     * Terapkan pencarian yang toleran: case-insensitive, mendukung banyak kata,
     * dan mencari di nama, SKU, deskripsi, kategori, merek/warna/material.
     */
    private function applySearch(Builder $query, string $search): void
    {
        $tokens = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($tokens)) {
            return;
        }

        // PostgreSQL tidak case-sensitive dengan LIKE, jadi pakai ILIKE.
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        foreach ($tokens as $token) {
            $like = '%'.$token.'%';

            $query->where(function (Builder $query) use ($like, $operator) {
                $query->where('name', $operator, $like)
                    ->orWhere('sku', $operator, $like)
                    ->orWhere('description', $operator, $like)
                    ->orWhere('badge', $operator, $like)
                    ->orWhere('color', $operator, $like)
                    ->orWhere('material', $operator, $like)
                    ->orWhereHas('category', fn (Builder $q) => $q->where('name', $operator, $like));
            });
        }
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

        $hasPurchased = auth()->check() && (
            auth()->user()->is_admin || auth()->user()->orders()
                ->where('status', '!=', 'cancelled')
                ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                ->exists()
        );

        return view('catalog.show', compact(
            'product',
            'relatedProducts',
            'ratingBreakdown',
            'isWishlisted',
            'userReview',
            'hasPurchased',
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
