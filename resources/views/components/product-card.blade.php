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
<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card transition duration-200 hover:-translate-y-1 hover:border-brand-300 hover:shadow-lift">
    {{-- Gambar produk --}}
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

        {{-- Badges pojok kiri atas --}}
        <div class="pointer-events-none absolute left-2.5 top-2.5 flex flex-col items-start gap-1">
            <span class="inline-flex items-center gap-1 rounded-md bg-purple-700/90 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm backdrop-blur">
                <svg class="h-3 w-3"><use href="#i-badge-check"/></svg>
                Official
            </span>
            @if ($product->hasDiscount())
                <span class="rounded-md bg-danger-500 px-1.5 py-0.5 text-[10px] font-extrabold text-white shadow-sm">
                    -{{ $product->discountPercent() }}%
                </span>
            @endif
            @if ($product->badge)
                <span class="rounded-md bg-ink-900/85 px-1.5 py-0.5 text-[10px] font-semibold text-white backdrop-blur">
                    {{ $product->badge }}
                </span>
            @endif
        </div>

        @auth
            <form method="POST"
                  action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}"
                  class="absolute right-2.5 top-2.5 z-10">
                @csrf
                @if ($isWishlisted)@method('DELETE')@endif
                <button type="submit"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-white/70 bg-white/90 shadow-sm backdrop-blur transition hover:scale-110 hover:bg-white"
                        aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                    <svg class="h-4 w-4 {{ $isWishlisted ? 'text-danger-500' : 'text-ink-400' }}" viewBox="0 0 24 24" aria-hidden="true">
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

    {{-- Detail & Harga --}}
    <div class="flex flex-1 flex-col gap-1.5 p-3 sm:p-3.5">
        {{-- Kategori --}}
        <span class="text-[10px] font-semibold uppercase tracking-wider text-brand-600 truncate">{{ $product->category->name }}</span>

        {{-- Judul produk --}}
        <h3 class="line-clamp-2 min-h-[2.5rem] text-xs font-semibold leading-snug text-ink-900 sm:text-sm">
            <a href="{{ route('products.show', $product) }}" class="transition hover:text-brand-600">{{ $product->name }}</a>
        </h3>

        {{-- Harga & Diskon --}}
        <div class="pt-0.5">
            <div class="text-sm font-extrabold text-ink-900 sm:text-base">
                Rp{{ number_format($product->price, 0, ',', '.') }}
            </div>
            @if ($product->hasDiscount())
                <div class="flex items-center gap-1.5 text-[11px] text-ink-400">
                    <span class="line-through">Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}</span>
                    <span class="rounded bg-danger-50 px-1 font-bold text-danger-600 text-[10px]">-{{ $product->discountPercent() }}%</span>
                </div>
            @endif
        </div>

        {{-- Badge Bebas Ongkir (Tokopedia Style) & Lokasi --}}
        <div class="flex flex-wrap items-center gap-1.5 pt-0.5 text-[10px]">
            <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 font-bold text-emerald-700">
                <svg class="h-3 w-3"><use href="#i-truck"/></svg>
                Bebas Ongkir
            </span>
            <span class="inline-flex items-center gap-0.5 text-ink-400">
                <svg class="h-3 w-3 text-ink-400"><use href="#i-map-pin"/></svg>
                Kota Yogyakarta
            </span>
        </div>

        {{-- Rating & Terjual (Shopee / Tokopedia Style) --}}
        <div class="flex items-center gap-1.5 text-[11px] text-ink-500 pt-0.5">
            @if ($reviewCount > 0)
                <span class="inline-flex items-center gap-0.5 text-amber-500">
                    <svg class="h-3.5 w-3.5 fill-current"><use href="#i-star-filled"/></svg>
                    <b class="text-ink-800">{{ number_format($rating, 1, ',', '.') }}</b>
                </span>
                <span class="text-ink-300">&bull;</span>
                <span>{{ $sold }} terjual</span>
            @else
                <span class="inline-flex items-center gap-0.5 text-ink-400">
                    <svg class="h-3.5 w-3.5 text-ink-300"><use href="#i-star"/></svg>
                    <span>Terjual {{ $sold }}</span>
                </span>
            @endif
        </div>

        {{-- Tombol Beli / Keranjang --}}
        <div class="mt-auto pt-2">
            @if ($product->stock > 0)
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-brand-700 active:scale-[0.98]">
                        <svg class="h-3.5 w-3.5"><use href="#i-cart"/></svg>
                        + Keranjang
                    </button>
                </form>
            @else
                <button type="button" disabled
                        class="flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-ink-100 px-3 py-1.5 text-xs font-semibold text-ink-400">
                    Stok Habis
                </button>
            @endif
        </div>
    </div>
</article>
