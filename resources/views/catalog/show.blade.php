@extends('layouts.app')

@section('title', $product->name.' — '.config('app.name'))

@section('content')
@php
    $rating = $product->averageRating();
    $reviewCount = $product->reviews->count();
    $infoRows = collect([
        'Kategori' => $product->category->name,
        'SKU' => $product->sku,
        'Berat' => $product->weight_grams ? $product->weight_grams.' gram' : null,
        'Material' => $product->material,
        'Warna' => $product->color,
        'Dimensi' => $product->dimensions,
    ])->filter();
@endphp

<div class="breadcrumb">
    <a href="{{ route('home') }}">Beranda</a><span>/</span>
    <a href="{{ route('home', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a><span>/</span>
    <b>{{ $product->name }}</b>
</div>

<section class="product-detail">
    <div class="detail-gallery">
        <div class="detail-visual">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            @else
                <span>{{ strtoupper(substr($product->category->name, 0, 2)) }}</span>
                <small>{{ $product->sku }}</small>
            @endif
        </div>
        @if ($product->image_url)
            <div class="detail-thumbs" aria-hidden="true">
                @for ($i = 0; $i < 3; $i++)
                    <span class="detail-thumb {{ $i === 0 ? 'is-active' : '' }}"><img src="{{ $product->image_url }}" alt=""></span>
                @endfor
            </div>
        @endif
    </div>

    <div class="detail-info">
        <span class="section-code">{{ $product->category->name }} / {{ $product->sku }}</span>
        <h1>{{ $product->name }}</h1>

        <div class="detail-rating">
            @if ($reviewCount > 0)
                @include('components.stars', ['rating' => $rating])
                <strong>{{ number_format($rating, 1, ',', '.') }}</strong>
                <a href="#ulasan">({{ $reviewCount }} ulasan)</a>
            @else
                <span>Belum ada ulasan</span>
            @endif
            <span class="detail-stock @if ($product->stock === 0) out-of-stock @endif">
                {{ $product->stock > 0 ? $product->stock.' stok tersedia' : 'Stok habis' }}
            </span>
        </div>

        <p class="detail-price">
            <strong>Rp{{ number_format($product->price, 0, ',', '.') }}</strong>
            @if ($product->hasDiscount())
                <del>Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}</del>
                <b class="detail-discount">Hemat {{ $product->discountPercent() }}%</b>
            @endif
        </p>

        <p class="detail-description">{{ $product->description }}</p>

        @auth
            <form class="detail-actions" method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="quantity-stepper" data-stepper>
                    <button type="button" data-step="down" aria-label="Kurangi jumlah"><svg class="icon" aria-hidden="true"><use href="#i-minus"/></svg></button>
                    <input id="quantity" name="quantity" type="number" value="1" min="1" max="{{ max(1, $product->stock) }}" @disabled($product->stock === 0)>
                    <button type="button" data-step="up" aria-label="Tambah jumlah"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button>
                </div>

                <button class="primary-button" type="submit" @disabled($product->stock === 0)>
                    <svg class="icon" aria-hidden="true"><use href="#i-cart"/></svg>
                    {{ $product->stock > 0 ? 'Tambah ke Keranjang' : 'Stok habis' }}
                </button>

                <button class="secondary-button" type="submit" name="buy_now" value="1" @disabled($product->stock === 0)>Beli Sekarang</button>
            </form>

            <form class="detail-wishlist" method="POST" action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}">
                @csrf
                @if ($isWishlisted)@method('DELETE')@endif
                <button class="icon-button detail-wish {{ $isWishlisted ? 'is-active' : '' }}" type="submit"
                        aria-label="{{ $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
                    <svg class="icon" aria-hidden="true"><use href="#i-heart"/></svg>
                    <span>{{ $isWishlisted ? 'Tersimpan di wishlist' : 'Simpan ke wishlist' }}</span>
                </button>
            </form>

            @error('quantity')<p class="field-error">{{ $message }}</p>@enderror
        @else
            <div class="detail-actions">
                <a class="primary-button button-link" href="{{ route('login') }}">Masuk untuk membeli</a>
                <a class="secondary-button button-link" href="{{ route('register') }}">Daftar akun</a>
            </div>
        @endauth
    </div>
</section>

@if ($infoRows->isNotEmpty())
<section class="home-section">
    <header class="home-section-heading"><div><span class="section-code">RINCIAN</span><h2>Informasi produk</h2></div></header>
    <dl class="info-table">
        @foreach ($infoRows as $label => $value)
            <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
        @endforeach
    </dl>
</section>
@endif

<section class="home-section" id="ulasan">
    <header class="home-section-heading">
        <div><span class="section-code">ULASAN</span><h2>Ulasan pembeli</h2></div>
        <span>{{ $reviewCount }} ulasan</span>
    </header>

    <div class="review-layout">
        <aside class="review-summary">
            @if ($reviewCount > 0)
                <strong class="review-average">{{ number_format($rating, 1, ',', '.') }}</strong>
                @include('components.stars', ['rating' => $rating])
                <small>dari {{ $reviewCount }} ulasan</small>
                <ul class="review-breakdown">
                    @foreach ($ratingBreakdown as $star => $count)
                        <li>
                            <span>{{ $star }}<svg class="icon" aria-hidden="true"><use href="#i-star"/></svg></span>
                            <span class="review-bar"><span style="width: {{ $reviewCount > 0 ? round($count / $reviewCount * 100) : 0 }}%"></span></span>
                            <b>{{ $count }}</b>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>Produk ini belum memiliki ulasan.</p>
            @endif
        </aside>

        <div class="review-list">
            @auth
                @php
                    $editing = $userReview !== null;
                @endphp
                <form class="review-form" method="POST" action="{{ route('reviews.store', $product) }}">
                    @csrf
                    <h3>{{ $editing ? 'Ubah ulasan Anda' : 'Tulis ulasan' }}</h3>
                    <div class="review-input">
                        <label for="rating">Rating</label>
                        <select id="rating" name="rating">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating', $userReview?->rating) === $i)>{{ $i }} bintang</option>
                            @endfor
                        </select>
                    </div>
                    <div class="field">
                        <label for="comment">Komentar <small>opsional</small></label>
                        <textarea id="comment" name="comment" rows="3" maxlength="500">{{ old('comment', $userReview?->comment) }}</textarea>
                    </div>
                    <button class="primary-button" type="submit">{{ $editing ? 'Perbarui ulasan' : 'Kirim ulasan' }}</button>
                </form>
            @else
                <p class="review-login"><a href="{{ route('login') }}">Masuk</a> untuk menulis ulasan.</p>
            @endauth

            @forelse ($product->reviews as $review)
                <article class="review-item">
                    <div class="review-item-head">
                        <span class="avatar" aria-hidden="true">{{ $review->user->initials() }}</span>
                        <div>
                            <strong>{{ $review->user->username }}</strong>
                            @include('components.stars', ['rating' => $review->rating])
                        </div>
                        <time>{{ $review->created_at->translatedFormat('d M Y') }}</time>
                    </div>
                    @if ($review->comment)
                        <p>{{ $review->comment }}</p>
                    @endif
                    @auth
                        @if ($review->user_id === auth()->id() || auth()->user()->is_admin)
                            <form method="POST" action="{{ route('reviews.destroy', $review) }}">
                                @csrf
                                @method('DELETE')
                                <button class="remove-button" type="submit">Hapus ulasan</button>
                            </form>
                        @endif
                    @endauth
                </article>
            @empty
                <p class="review-empty">Jadilah yang pertama mengulas produk ini.</p>
            @endforelse
        </div>
    </div>
</section>

@if ($relatedProducts->isNotEmpty())
<section class="home-section related-section">
    <div class="home-section-heading"><div><span class="section-code">TERKAIT</span><h2>Produk dalam kategori yang sama</h2></div></div>
    <div class="product-grid">
        @foreach ($relatedProducts as $related)
            @include('components.product-card', ['product' => $related])
        @endforeach
    </div>
</section>
@endif
@endsection
