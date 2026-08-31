<article class="product-card">
    <a class="product-visual" href="{{ route('products.show', $product) }}" tabindex="-1" aria-hidden="true">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="" loading="lazy">
        @else
            <span>{{ strtoupper(substr($product->category->name, 0, 2)) }}</span>
            <small>{{ $product->sku }}</small>
        @endif
        @if ($product->is_featured)
            <b>REKOMENDASI</b>
        @endif
    </a>
    <div class="product-info">
        <span class="product-category">{{ $product->category->name }}</span>
        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <div class="product-meta">
            <strong>Rp{{ number_format($product->price, 0, ',', '.') }}</strong>
            <span @class(['out-of-stock' => $product->stock === 0])>{{ $product->stock > 0 ? $product->stock.' stok' : 'Habis' }}</span>
        </div>
    </div>
</article>
