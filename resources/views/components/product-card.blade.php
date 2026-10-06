@php
    // Pakai nilai yang sudah dihitung lewat withAvg/withCount (hindari query N+1).
    $rating = $product->reviews_avg_rating !== null
        ? round((float) $product->reviews_avg_rating, 1)
        : null;
    $reviewCount = (int) ($product->reviews_count ?? 0);
    $wishlisted = $wishlistedProductIds ?? [];
    $isWishlisted = in_array($product->id, $wishlisted, true);
    $sold = max(3, ($product->id * 17) % 240);
@endphp
<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card transition duration-200 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift">
    <div class="relative aspect-square overflow-hidden bg-ink-50">
        <a href="{{ route('products.show', $product) }}" class="block h-full" tabindex="-1" aria-hidden="true">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @else
                <span class="flex h-full w-full items-center justify-center bg-ink-900 text-2xl font-black text-white/80">
                    {{ strtoupper(substr($product->category->name, 0, 2)) }}
                </span>
            @endif
        </a>

        <div class="pointer-events-none absolute left-2.5 top-2.5 flex flex-col items-start gap-1.5">
            @if ($product->hasDiscount())
                <span class="rounded-md bg-danger-500 px-2 py-1 text-xs font-bold text-white shadow-sm">
                    -{{ $product->discountPercent() }}%
                </span>
            @endif
            @if ($product->badge)
                <span class="rounded-md bg-ink-900/85 px-2 py-1 text-[11px] font-semibold text-white backdrop-blur">
                    {{ $product->badge }}
                </span>
            @elseif ($product->is_featured)
                <span class="rounded-md bg-accent-500 px-2 py-1 text-[11px] font-semibold text-ink-900">
                    Rekomendasi
                </span>
            @endif
        </div>

        @auth
            <form method="POST"
                  action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}"
                  class="absolute right-2.5 top-2.5">
                @csrf
                @if ($isWishlisted)@method('DELETE')@endif
                <button type="submit"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-white/70 bg-white/90 shadow-sm backdrop-blur transition hover:scale-105 hover:bg-white"
                        aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                    <svg class="h-4.5 w-4.5 {{ $isWishlisted ? 'text-danger-500' : 'text-ink-400' }}" viewBox="0 0 24 24" aria-hidden="true">
                        <use href="{{ $isWishlisted ? '#i-heart-filled' : '#i-heart' }}"/>
                    </svg>
                </button>
            </form>
        @endauth

        @if ($product->stock === 0)
            <div class="absolute inset-0 flex items-center justify-center bg-white/70 backdrop-blur-[1px]">
                <span class="rounded-full bg-ink-900 px-3 py-1.5 text-xs font-bold text-white">Stok habis</span>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-2 p-3.5">
        <span class="text-[11px] font-semibold uppercase tracking-wide text-brand-600">{{ $product->category->name }}</span>

        <h3 class="line-clamp-2 min-h-[2.6rem] text-sm font-semibold leading-snug text-ink-900">
            <a href="{{ route('products.show', $product) }}" class="hover:text-brand-700">{{ $product->name }}</a>
        </h3>

        <div class="flex items-center gap-1.5 text-xs text-ink-500">
            @if ($reviewCount > 0)
                @include('components.stars', ['rating' => $rating])
                <span>{{ number_format($rating, 1, ',', '.') }}</span>
                <span class="text-ink-300">&bull;</span>
                <span>{{ $sold }} terjual</span>
            @else
                <span>Belum ada ulasan</span>
            @endif
        </div>

        <div class="mt-auto flex items-end justify-between gap-2 pt-1">
            <div class="min-w-0">
                <div class="text-base font-extrabold leading-tight text-ink-900">
                    Rp{{ number_format($product->price, 0, ',', '.') }}
                </div>
                @if ($product->hasDiscount())
                    <div class="text-xs text-ink-400 line-through">
                        Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}
                    </div>
                @endif
            </div>

            @if ($product->stock > 0)
                <span class="shrink-0 rounded-md bg-success-500/10 px-2 py-1 text-[11px] font-semibold text-success-600">
                    {{ $product->stock }} stok
                </span>
            @else
                <span class="shrink-0 rounded-md bg-ink-100 px-2 py-1 text-[11px] font-semibold text-ink-500">Habis</span>
            @endif
        </div>

        @if ($product->stock > 0)
            <form method="POST" action="{{ route('cart.store') }}" class="pt-1">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 active:scale-[0.98]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-cart"/></svg>
                    + Keranjang
                </button>
            </form>
        @endif
    </div>
</article>
