@extends('layouts.app')

@section('title', 'Etalase — Toko Kebutuhan Rumah dan Kerja')

@section('content')
<div class="store-notice">
    <span>Gratis ongkir untuk pesanan mulai Rp300.000</span>
    <span>COD dan transfer bank</span>
</div>

@php
    $banners = [
        [
            'code' => 'PROMO PENGIRIMAN',
            'title' => 'Gratis ongkir mulai Rp300.000',
            'subtitle' => 'Berlaku untuk semua produk di katalog. Bisa COD atau transfer bank.',
            'cta' => 'Lihat katalog',
            'image' => 'images/products/tas-lipat.jpg',
            'link' => '#catalog',
        ],
        [
            'code' => 'KATEGORI KERJA',
            'title' => 'Rapikan meja kerja Anda',
            'subtitle' => 'Dudukan laptop, notebook grid, dan organizer kabel siap dipakai.',
            'cta' => 'Jelajahi kategori',
            'image' => 'images/products/dudukan-laptop.jpg',
            'link' => route('home', ['category' => 'kerja']),
        ],
        [
            'code' => 'SIAP DIKIRIM',
            'title' => 'Hadiah siap kirim bulan ini',
            'subtitle' => 'Paket kopi drip dan lilin aromaterapi pilihan untuk kiriman.',
            'cta' => 'Lihat hadiah',
            'image' => 'images/products/kopi.jpg',
            'link' => route('home', ['category' => 'hadiah']),
        ],
    ];

    $hotDealEndsAt = now()->endOfMonth()->setTime(23, 59, 59);
@endphp

@if (! request('q') && ! request('category'))
    <section class="banner-carousel" aria-label="Banner promo">
        <div class="banner-track">
            @foreach ($banners as $index => $banner)
                <a class="banner-slide {{ $index === 0 ? 'is-active' : '' }}" href="{{ $banner['link'] }}" style="background-image: linear-gradient(92deg, rgba(11, 13, 23, .93) 4%, rgba(26, 47, 168, .62) 48%, rgba(26, 47, 168, 0) 78%), url('{{ asset($banner['image']) }}')">
                    <div class="banner-copy">
                        <span class="section-code">{{ $banner['code'] }}</span>
                        <strong>{{ $banner['title'] }}</strong>
                        <small>{{ $banner['subtitle'] }}</small>
                        <span class="banner-cta">{{ $banner['cta'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <button class="banner-nav banner-prev" type="button" aria-label="Banner sebelumnya">&lsaquo;</button>
        <button class="banner-nav banner-next" type="button" aria-label="Banner berikutnya">&rsaquo;</button>
        <div class="banner-dots">
            @foreach ($banners as $index => $banner)
                <button type="button" aria-label="Ke banner {{ $index + 1 }}" class="{{ $index === 0 ? 'is-active' : '' }}"></button>
            @endforeach
        </div>
    </section>
@endif

@if (! request('q') && ! request('category') && $heroProduct)
    <section class="storefront-hero">
        <div class="hero-copy">
            <span class="section-code">KOLEKSI PILIHAN / {{ now()->year }}</span>
            <h1>Kebutuhan rumah dan meja kerja.</h1>
            <p>Temukan perlengkapan yang tersedia di gudang. Harga dan jumlah stok diperbarui dari katalog.</p>
            <div class="hero-actions">
                <a class="primary-button button-link fit" href="#catalog">Lihat katalog</a>
                <a class="hero-text-link" href="{{ route('products.show', $heroProduct) }}">Lihat produk pilihan</a>
            </div>
        </div>
        <a class="hero-product" href="{{ route('products.show', $heroProduct) }}">
            <img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}">
            <span class="hero-product-label">
                <small>{{ $heroProduct->category->name }}</small>
                <strong>{{ $heroProduct->name }}</strong>
                <b>Rp{{ number_format($heroProduct->price, 0, ',', '.') }}</b>
            </span>
        </a>
    </section>

    <section class="service-strip" aria-label="Informasi layanan">
        <div><b>01</b><span><strong>Stok tercatat</strong><small>Jumlah diperiksa saat checkout</small></span></div>
        <div><b>02</b><span><strong>Dua metode pembayaran</strong><small>COD atau transfer bank</small></span></div>
        <div><b>03</b><span><strong>Riwayat pesanan</strong><small>Status tersimpan di akun</small></span></div>
    </section>

    <section class="home-section category-section">
        <header class="home-section-heading">
            <div><span class="section-code">DEPARTEMEN</span><h2>Belanja menurut kategori</h2></div>
            <span>{{ $categories->count() }} kategori</span>
        </header>
        <div class="home-categories">
            @foreach ($categories as $index => $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}">
                    <small>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</small>
                    <strong>{{ $category->name }}</strong>
                    <span>{{ $category->products_count }} produk</span>
                </a>
            @endforeach
        </div>
    </section>

    @if ($featuredProducts->isNotEmpty())
        <section class="home-section featured-section">
            <header class="home-section-heading">
                <div><span class="section-code">REKOMENDASI</span><h2>Produk pilihan</h2></div>
                <a href="#catalog">Lihat semua produk</a>
            </header>
            <div class="product-grid featured-grid">
                @foreach ($featuredProducts as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <section class="home-section hot-deal-section">
        <header class="home-section-heading">
            <div><span class="section-code">HOT DEAL</span><h2>Promo akhir bulan</h2></div>
            <span>Berakhir {{ $hotDealEndsAt->translatedFormat('j F Y') }}</span>
        </header>
        <div class="hot-deal">
            <div class="hot-deal-copy">
                <h3>Penawaran berakhir dalam</h3>
                <div class="countdown" data-deadline="{{ $hotDealEndsAt->toIso8601String() }}">
                    <div class="count-unit"><strong data-unit="days">00</strong><span>Hari</span></div>
                    <div class="count-unit"><strong data-unit="hours">00</strong><span>Jam</span></div>
                    <div class="count-unit"><strong data-unit="minutes">00</strong><span>Menit</span></div>
                    <div class="count-unit"><strong data-unit="seconds">00</strong><span>Detik</span></div>
                </div>
                <p class="hot-deal-note">Berlaku sampai {{ $hotDealEndsAt->translatedFormat('j F Y') }} atau selama stok masih tersedia.</p>
                <a class="secondary-button button-link fit" href="#catalog">Belanja sekarang</a>
            </div>
            <div class="hot-deal-products">
                @foreach ($featuredProducts->take(3) as $product)
                    <a class="deal-product" href="{{ route('products.show', $product) }}">
                        <span class="deal-visual" aria-hidden="true">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="">
                            @else
                                <b>{{ strtoupper(substr($product->category->name, 0, 2)) }}</b>
                            @endif
                        </span>
                        <span class="deal-identity">
                            <small>{{ $product->category->name }}</small>
                            <strong>{{ $product->name }}</strong>
                            <span class="deal-meta">
                                <b>Rp{{ number_format($product->price, 0, ',', '.') }}</b>
                                <span @class(['out-of-stock' => $product->stock === 0])>{{ $product->stock > 0 ? $product->stock.' stok' : 'Habis' }}</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="catalog-area" id="catalog">
    <header class="catalog-title">
        <div>
            <span class="section-code">KATALOG / {{ str_pad($products->total(), 2, '0', STR_PAD_LEFT) }} PRODUK</span>
            <h2>{{ request('q') || request('category') ? 'Hasil pencarian' : 'Semua produk' }}</h2>
        </div>
        @if (request('q') || request('category'))
            <a class="text-link" href="{{ route('home') }}">Hapus filter</a>
        @endif
    </header>

    <section class="catalog-tools" aria-label="Pencarian dan filter produk">
        <form class="search-form" method="GET" action="{{ route('home') }}">
            <label for="q">Cari produk</label>
            <div>
                <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Nama, SKU, atau deskripsi">
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button type="submit">Cari</button>
            </div>
        </form>

        <nav class="category-filter" aria-label="Filter kategori">
            <a href="{{ route('home', array_filter(['q' => request('q')])) }}" @class(['active' => ! request('category')])>Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('home', array_filter(['q' => request('q'), 'category' => $category->slug])) }}" @class(['active' => request('category') === $category->slug])>
                    {{ $category->name }} <small>{{ $category->products_count }}</small>
                </a>
            @endforeach
        </nav>
    </section>

    <section class="product-grid" aria-label="Daftar produk">
        @forelse ($products as $product)
            @include('components.product-card', ['product' => $product])
        @empty
            <div class="empty-state">
                <h2>Produk tidak ditemukan</h2>
                <p>Ubah kata pencarian atau pilih kategori lain.</p>
                <a class="text-link" href="{{ route('home') }}">Lihat semua produk</a>
            </div>
        @endforelse
    </section>

    @if ($products->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($products->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $products->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</b>
            @if ($products->hasMorePages())<a href="{{ $products->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</section>
@endsection
