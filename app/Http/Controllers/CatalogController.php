<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Voucher;
use App\Support\CatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
        ]);

        $sort = $filters['sort'] ?? 'featured';

        $products = $this->productQuery()
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->when($filters['category'] ?? null, function (Builder $query, string $slug) {
                $query->whereHas('category', fn (Builder $query) => $query->where('slug', $slug));
            })
            ->when($filters['min_price'] ?? null, fn (Builder $query, int|string $price) => $query->where('price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn (Builder $query, int|string $price) => $query->where('price', '<=', $price))
            ->when($sort === 'latest', fn ($q) => $q->latest('id'))
            ->when($sort === 'price_asc', fn ($q) => $q->orderBy('price', 'asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
            ->when($sort === 'featured', fn ($q) => $q->orderByDesc('is_featured')->orderBy('name'))
            ->paginate(12)
            ->withQueryString();

        $categories = CatalogCache::remember('home.categories', fn () => Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn (Builder $query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get());

        $featuredProducts = CatalogCache::remember('home.featured', fn () => $this->productQuery()
            ->where('is_featured', true)
            ->orderBy('name')
            ->limit(4)
            ->get());

        // Flash sale harian: urutan acak yang ditentukan tanggal (bukan random per request),
        // jadi daftarnya sama sepanjang hari dan benar-benar berganti setiap tengah malam (WIB).
        $flashSaleProducts = CatalogCache::remember('home.flash-sale.'.now()->format('Y-m-d'), fn () => $this->productQuery()
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->where('stock', '>', 0)
            ->orderByRaw('(compare_at_price - price) DESC')
            ->limit(24)
            ->get()
            ->filter(fn (Product $product) => $product->hasDiscount())
            ->sortBy(fn (Product $product) => crc32($product->id.'|'.now()->format('Y-m-d')))
            ->take(6)
            ->sortByDesc(fn (Product $product) => $product->discountPercent())
            ->values());
        $flashSaleEndsAt = now()->endOfDay();

        // Saring di sini juga karena voucher bisa kedaluwarsa tanpa ada data yang berubah.
        $vouchers = CatalogCache::remember('home.vouchers', fn () => Voucher::query()
            ->where('is_active', true)
            ->orderBy('min_spend')
            ->get())
            ->filter(fn (Voucher $voucher) => $voucher->isUsable())
            ->take(4)
            ->values();

        $heroProduct = $featuredProducts->firstWhere('image_url', '!=', null) ?? $featuredProducts->first();
        $categoryCovers = CatalogCache::remember('home.covers', fn () => $this->categoryCovers());
        $heroSlides = $this->heroSlides($heroProduct, $categories, $flashSaleProducts);
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
            'flashSaleEndsAt',
            'vouchers',
            'heroProduct',
            'heroSlides',
            'categoryCovers',
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

    /**
     * Slide banner beranda, masing-masing menampilkan satu produk asli.
     * Slide tanpa produk berfoto dilewati.
     */
    private function heroSlides(?Product $heroProduct, \Illuminate\Support\Collection $categories, \Illuminate\Support\Collection $promoProducts): array
    {
        $diecastCategory = $categories->first(fn (Category $category) => $category->icon() === 'i-car');
        $diecastProduct = $diecastCategory
            ? $this->productQuery()
                ->where('category_id', $diecastCategory->id)
                ->whereNotNull('image_url')
                ->orderByDesc('is_featured')
                ->orderBy('id')
                ->first()
            : null;
        $promoProduct = $promoProducts->sortByDesc(fn (Product $product) => $product->discountPercent())->first();

        return collect([
            [
                'eyebrow' => 'Koleksi '.now()->year,
                'title' => 'Gear terbaik.',
                'subtitle' => 'Koleksi terpilih.',
                'body' => 'Konsol, komponen PC, audio, dan diecast original, dikurasi untuk gamer dan kolektor.',
                'cta' => ['Belanja sekarang', '#produk'],
                'secondary' => ['Lihat promo', route('promo')],
                'tag' => 'Pilihan editor',
                'tone' => 'text-brand-600',
                'product' => $heroProduct,
            ],
            [
                'eyebrow' => 'Untuk kolektor',
                'title' => 'Detail skala 1:64.',
                'subtitle' => 'Edisi terbatas.',
                'body' => 'Mini GT dan Hot Wheels original, dikemas aman sampai ke rak koleksimu.',
                'cta' => ['Lihat diecast', $diecastCategory ? route('home', ['category' => $diecastCategory->slug]) : route('categories.index')],
                'secondary' => ['Semua kategori', route('categories.index')],
                'tag' => 'Koleksi diecast',
                'tone' => 'text-accent-600',
                'product' => $diecastProduct,
            ],
            [
                'eyebrow' => 'Promo pilihan',
                'title' => 'Hemat hingga '.($promoProduct?->discountPercent() ?? 0).'%.',
                'subtitle' => 'Selama stok ada.',
                'body' => 'Harga spesial untuk gear favorit. Pakai voucher toko untuk potongan tambahan.',
                'cta' => ['Lihat promo', route('promo')],
                'secondary' => ['Klaim voucher', '#vouchers'],
                'tag' => 'Diskon terbesar',
                'tone' => 'text-danger-600',
                'product' => $promoProduct,
            ],
        ])->filter(fn (array $slide) => $slide['product']?->image_url)->values()->all();
    }

    /**
     * Satu foto produk per kategori untuk dijadikan sampul kartu kategori.
     *
     * @return array<int, string>
     */
    private function categoryCovers(): array
    {
        return Product::query()
            ->where('is_active', true)
            ->whereNotNull('image_url')
            ->orderByDesc('is_featured')
            ->orderBy('id')
            ->get(['category_id', 'image_url'])
            ->unique('category_id')
            ->pluck('image_url', 'category_id')
            ->all();
    }

    private function wishlistedIds(\Illuminate\Support\Collection $products): array
    {
        if (! auth()->check()) {
            return [];
        }

        $ids = $products->pluck('id')->unique();

        return auth()->user()->wishlists()->whereIn('product_id', $ids)->pluck('product_id')->all();
    }

    /**
     * Saran produk saat pengguna mengetik di kolom pencarian.
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) ($request->validate(['q' => 'nullable|string|max:100'])['q'] ?? ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->tap(fn (Builder $builder) => $this->applySearch($builder, $query))
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'slug', 'image_url']);

        return response()->json([
            'suggestions' => $products->map(fn (Product $product) => [
                'name' => $product->name,
                'url' => route('products.show', $product),
                'image_url' => $product->image_url,
            ])->all(),
        ]);
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

        $storeProducts = CatalogCache::remember("product.{$product->id}.store-others", fn () => $this->productQuery()
            ->where('category_id', '!=', $product->category_id)
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(4)
            ->get());

        $wishlistedProductIds = $this->wishlistedIds($relatedProducts->concat($storeProducts));

        $ratingBreakdown = collect(range(5, 1))->mapWithKeys(fn (int $star) => [
            $star => $product->reviews->where('rating', $star)->count(),
        ]);

        $isWishlisted = auth()->check()
            && auth()->user()->wishlists()->where('product_id', $product->id)->exists();

        $userReview = auth()->check()
            ? $product->reviews->firstWhere('user_id', auth()->id())
            : null;

        $canReview = auth()->check() && auth()->user()->canReview($product);

        return view('catalog.show', compact(
            'product',
            'relatedProducts',
            'storeProducts',
            'ratingBreakdown',
            'isWishlisted',
            'userReview',
            'canReview',
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

        $categoryCovers = $this->categoryCovers();

        return view('catalog.categories', compact('categories', 'categoryCovers'));
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
