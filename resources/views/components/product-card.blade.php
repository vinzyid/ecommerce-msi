@php
    // Pakai nilai yang sudah dihitung lewat withAvg/withCount (hindari query N+1).
    $rating = $product->reviews_avg_rating !== null
        ? round((float) $product->reviews_avg_rating, 1)
        : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);
    $wishlisted = $wishlistedProductIds ?? [];
    $isWishlisted = in_array($product->id, $wishlisted, true);
    $categoryName = $product->category->name;
    // Satu label saja: diskon lebih penting daripada badge bebas teks.
    $label = $product->hasDiscount() ? '-'.$product->discountPercent().'%' : $product->badge;
@endphp
<div data-reveal-item class="h-full">
<article class="group relative flex h-full flex-col">
    {{-- Foto produk di atas latar abu lembut; mix-blend menyatukan foto berlatar putih --}}
    <a href="{{ route('products.show', $product) }}" tabindex="-1" aria-hidden="true"
       class="relative block aspect-square overflow-hidden rounded-2xl bg-ink-50">
        <span class="absolute inset-0 grid place-items-center text-ink-300">
            <svg class="h-10 w-10" aria-hidden="true"><use href="#{{ $product->category->icon() }}"/></svg>
        </span>
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()"
                 class="relative h-full w-full object-contain p-[9%] mix-blend-multiply transition-[scale] duration-500 ease-(--ease-out) group-hover:scale-[1.04]">
        @endif

        @if ($label)
            <span @class([
                'absolute left-3 top-3 max-w-[calc(100%-3.5rem)] truncate whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold',
                'bg-white text-danger-600 shadow-xs' => $product->hasDiscount(),
                'bg-white/90 text-ink-700 shadow-xs' => ! $product->hasDiscount(),
            ])>{{ $label }}</span>
        @endif

        @if ($product->stock === 0)
            <span class="absolute inset-x-3 bottom-3 rounded-full bg-white/90 py-1.5 text-center text-xs font-medium text-ink-700 backdrop-blur">
                Stok habis
            </span>
        @endif
    </a>

    @auth
        <form method="POST"
              action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}"
              class="absolute right-3 top-3 z-10">
            @csrf
            @if ($isWishlisted)@method('DELETE')@endif
            <button type="submit"
                    class="grid h-8 w-8 place-items-center rounded-full bg-white/90 text-ink-500 shadow-xs backdrop-blur hover:text-ink-900 active:scale-90"
                    aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                <svg class="h-4 w-4 {{ $isWishlisted ? 'text-danger-500' : '' }}" aria-hidden="true">
                    <use href="{{ $isWishlisted ? '#i-heart-filled' : '#i-heart' }}"/>
                </svg>
            </button>
        </form>
    @endauth

    <div class="flex flex-1 flex-col px-1 pt-3">
        <p class="truncate text-xs text-ink-500" title="{{ $categoryName }}">{{ $categoryName }}</p>

        <h3 class="mt-0.5 line-clamp-2 min-h-[2lh] break-words text-sm font-medium leading-snug text-ink-900">
            <a href="{{ route('products.show', $product) }}" class="hover:text-brand-600">{{ $product->name }}</a>
        </h3>

        <div class="mt-1.5 flex flex-wrap items-baseline gap-x-2 tabular-nums">
            <span class="text-[15px] font-semibold text-ink-900">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
            @if ($product->hasDiscount())
                <span class="text-xs text-ink-400 line-through">Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}</span>
            @endif
        </div>

        @if ($reviewCount > 0)
            <p class="mt-1 flex items-center gap-1 text-xs text-ink-500">
                <svg class="h-3.5 w-3.5 text-ink-900" aria-hidden="true"><use href="#i-star-filled"/></svg>
                <span class="font-medium text-ink-700">{{ number_format($rating, 1, ',', '.') }}</span>
                <span>({{ $reviewCount }})</span>
            </p>
        @endif

        <div class="mt-auto pt-3">
            @if ($product->stock > 0)
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit"
                            class="w-full whitespace-nowrap rounded-full border border-ink-200 px-3 py-2 text-xs font-medium text-ink-900 hover:border-ink-900 hover:bg-ink-900 hover:text-white">
                        Tambah ke keranjang
                    </button>
                </form>
            @else
                <button type="button" disabled
                        class="w-full cursor-not-allowed whitespace-nowrap rounded-full border border-ink-100 px-3 py-2 text-xs font-medium text-ink-400">
                    Stok habis
                </button>
            @endif
        </div>
    </div>
</article>
</div>
