@php
    $rating = $product->rating_avg ?? $product->averageRating();
    $reviewCount = $product->reviews_count ?? $product->reviews()->count();
    $wishlisted = $wishlistedProductIds ?? [];
    $isWishlisted = in_array($product->id, $wishlisted, true);
@endphp
<article class="product-card">
    <a class="product-visual" href="{{ route('products.show', $product) }}" tabindex="-1" aria-hidden="true">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="" loading="lazy">
        @else
            <span>{{ strtoupper(substr($product->category->name, 0, 2)) }}</span>
            <small>{{ $product->sku }}</small>
        @endif

        <span class="card-badges">
            @if ($product->hasDiscount())
                <b class="card-badge card-badge-discount">-{{ $product->discountPercent() }}%</b>
            @endif
            @if ($product->badge)
                <b class="card-badge card-badge-label">{{ $product->badge }}</b>
            @elseif ($product->is_featured)
                <b class="card-badge card-badge-label">Rekomendasi</b>
            @endif
        </span>
    </a>

    @auth
        <form class="wishlist-toggle {{ $isWishlisted ? 'is-active' : '' }}" method="POST"
              action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}">
            @csrf
            @if ($isWishlisted)@method('DELETE')@endif
            <button type="submit" aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                <svg class="icon" aria-hidden="true"><use href="#i-heart"/></svg>
            </button>
        </form>
    @endauth

    <div class="product-info">
        <span class="product-category">{{ $product->category->name }}</span>
        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>

        <div class="product-rating">
            @if ($reviewCount > 0)
                @include('components.stars', ['rating' => $rating])
                <span>{{ number_format($rating, 1, ',', '.') }} ({{ $reviewCount }})</span>
            @else
                <span class="product-rating-empty">Belum ada ulasan</span>
            @endif
        </div>

        <div class="product-meta">
            <span class="product-price">
                <strong>Rp{{ number_format($product->price, 0, ',', '.') }}</strong>
                @if ($product->hasDiscount())
                    <del>Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}</del>
                @endif
            </span>
            <span class="product-stock @if ($product->stock === 0) out-of-stock @endif">
                {{ $product->stock > 0 ? $product->stock.' stok' : 'Habis' }}
            </span>
        </div>
    </div>
</article>
