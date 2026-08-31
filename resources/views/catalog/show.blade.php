@extends('layouts.app')

@section('title', $product->name.' — Etalase')

@section('content')
<div class="breadcrumb"><a href="{{ route('home') }}">Katalog</a><span>/</span><a href="{{ route('home', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a><span>/</span><b>{{ $product->name }}</b></div>

<section class="product-detail">
    <div class="detail-visual">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
        @else
            <span>{{ strtoupper(substr($product->category->name, 0, 2)) }}</span>
            <small>{{ $product->sku }}</small>
        @endif
    </div>

    <div class="detail-info">
        <span class="section-code">{{ $product->category->name }} / {{ $product->sku }}</span>
        <h1>{{ $product->name }}</h1>
        <p class="detail-price">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
        <p class="detail-description">{{ $product->description }}</p>

        <div class="stock-line">
            <span>Status stok</span>
            <strong>{{ $product->stock > 0 ? $product->stock.' tersedia' : 'Stok habis' }}</strong>
        </div>

        @auth
            <form class="add-cart-form" method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <label for="quantity">Jumlah</label>
                <input id="quantity" name="quantity" type="number" value="1" min="1" max="{{ max(1, $product->stock) }}" @disabled($product->stock === 0)>
                <button class="primary-button" type="submit" @disabled($product->stock === 0)>{{ $product->stock > 0 ? 'Tambah ke cart' : 'Stok habis' }}</button>
            </form>
            @error('quantity')<p class="field-error">{{ $message }}</p>@enderror
        @else
            <a class="primary-button button-link" href="{{ route('login') }}">Masuk untuk membeli</a>
        @endauth
    </div>
</section>

@if ($relatedProducts->isNotEmpty())
<section class="related-section">
    <div class="section-heading"><h2>Produk dalam kategori yang sama</h2></div>
    <div class="product-grid compact-grid">
        @foreach ($relatedProducts as $related)
            <article class="product-card">
                <a class="product-visual" href="{{ route('products.show', $related) }}">
                    @if ($related->image_url)
                        <img src="{{ $related->image_url }}" alt="{{ $related->name }}" loading="lazy">
                    @else
                        <span>{{ strtoupper(substr($related->category->name, 0, 2)) }}</span>
                        <small>{{ $related->sku }}</small>
                    @endif
                </a>
                <div class="product-info">
                    <h3><a href="{{ route('products.show', $related) }}">{{ $related->name }}</a></h3>
                    <strong>Rp{{ number_format($related->price, 0, ',', '.') }}</strong>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif
@endsection
