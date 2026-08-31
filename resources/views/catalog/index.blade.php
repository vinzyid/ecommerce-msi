@extends('layouts.app')

@section('title', 'Etalase — Toko Kebutuhan Rumah dan Kerja')

@section('content')
<div class="store-notice">
    <span>Gratis ongkir untuk pesanan mulai Rp300.000</span>
    <span>COD dan transfer bank</span>
</div>

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
