@extends('layouts.app')

@section('title', config('app.name').' — Perlengkapan Rumah dan Meja Kerja')

@section('content')
@php
    $hasFilter = request('q') || request('category');
    $trustItems = [
        ['icon' => 'i-truck', 'title' => 'Pengiriman Cepat', 'text' => 'Kurir khusus dan terlacak'],
        ['icon' => 'i-lock', 'title' => 'Pembayaran Aman', 'text' => 'COD dan transfer bank'],
        ['icon' => 'i-package', 'title' => 'Stok Akurat', 'text' => 'Diperbarui setiap hari'],
        ['icon' => 'i-refresh', 'title' => 'Produk Terkurasi', 'text' => 'Dipilih dari koleksi pilihan'],
    ];
@endphp

<section class="notice-bar">
    <span>Gratis ongkir untuk pesanan mulai Rp300.000</span>
    <span>COD dan transfer bank</span>
</section>

@if (! $hasFilter)
    <section class="hero">
        <div class="hero-copy">
            <span class="section-code">KOLEKSI PILIHAN / {{ now()->year }}</span>
            <h1>Lengkapi rumah dan meja kerja impian Anda.</h1>
            <p>Temukan perlengkapan rumah, meja kerja, dan kebutuhan harian dengan desain yang rapi untuk menciptakan ruang terbaik.</p>
            <div class="hero-actions">
                <a class="primary-button button-link" href="#katalog">Lihat Produk <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"/></svg></a>
                <a class="secondary-button button-link" href="{{ route('categories.index') }}">Kategori</a>
            </div>

            <div class="hero-trust">
                @foreach ($trustItems as $item)
                    <div class="hero-trust-item">
                        <svg class="icon" aria-hidden="true"><use href="#{{ $item['icon'] }}"/></svg>
                        <span><strong>{{ $item['title'] }}</strong><small>{{ $item['text'] }}</small></span>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($heroProduct)
            <a class="hero-visual" href="{{ route('products.show', $heroProduct) }}">
                @if ($heroProduct->image_url)
                    <img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}">
                @else
                    <span class="hero-visual-mark">{{ strtoupper(substr($heroProduct->name, 0, 1)) }}</span>
                @endif
                <span class="hero-chip">
                    <span class="hero-chip-visual">
                        @if ($heroProduct->image_url)
                            <img src="{{ $heroProduct->image_url }}" alt="">
                        @endif
                    </span>
                    <span class="hero-chip-body">
                        <small>{{ $heroProduct->category->name }}</small>
                        <strong>{{ $heroProduct->name }}</strong>
                        <b>Rp{{ number_format($heroProduct->price, 0, ',', '.') }}</b>
                    </span>
                    <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"/></svg>
                </span>
            </a>
        @endif
    </section>

    <section class="home-section category-section">
        <header class="home-section-heading">
            <div><span class="section-code">KATEGORI</span><h2>Belanja berdasarkan kebutuhan</h2></div>
            <a href="{{ route('categories.index') }}">Lihat semua kategori</a>
        </header>
        <div class="category-tiles">
            @php
                $categoryTaglines = [
                    'kebutuhan-harian' => 'Perlengkapan yang dipakai tiap hari',
                    'rumah' => 'Rapikan dapur dan ruang tinggal',
                    'kerja' => 'Dukung aktivitas meja kerja',
                    'hadiah' => 'Siap diberikan kapan saja',
                ];
            @endphp
            @foreach ($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}">
                    <span class="category-tile-icon" aria-hidden="true">
                        <svg class="icon"><use href="#i-layers"/></svg>
                    </span>
                    <strong>{{ $category->name }}</strong>
                    <small>{{ $categoryTaglines[$category->slug] ?? $category->description }}</small>
                    <span class="category-tile-count">{{ $category->products_count }} produk</span>
                    <span class="category-tile-link">Jelajahi <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"/></svg></span>
                </a>
            @endforeach
        </div>
    </section>
@endif

<section class="home-section featured-section" id="katalog">
    <header class="home-section-heading">
        <div>
            <span class="section-code">{{ $hasFilter ? 'HASIL PENCARIAN' : 'PRODUK PILIHAN' }}</span>
            <h2>{{ $hasFilter ? 'Hasil pencarian' : 'Produk pilihan' }}</h2>
        </div>
        @if ($hasFilter)
            <a href="{{ route('home') }}">Hapus filter</a>
        @else
            <span>{{ $products->total() }} produk tersedia</span>
        @endif
    </header>

    @if ($hasFilter)
        <nav class="category-filter" aria-label="Filter kategori">
            <a href="{{ route('home', array_filter(['q' => request('q')])) }}" @class(['active' => ! request('category')])>Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('home', array_filter(['q' => request('q'), 'category' => $category->slug])) }}" @class(['active' => request('category') === $category->slug])>
                    {{ $category->name }} <small>{{ $category->products_count }}</small>
                </a>
            @endforeach
        </nav>
    @endif

    <div class="product-grid">
        @forelse ($products as $product)
            @include('components.product-card', ['product' => $product])
        @empty
            <div class="empty-state">
                <h2>Produk tidak ditemukan</h2>
                <p>Ubah kata pencarian atau pilih kategori lain.</p>
                <a class="text-link" href="{{ route('home') }}">Lihat semua produk</a>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($products->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $products->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</b>
            @if ($products->hasMorePages())<a href="{{ $products->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</section>

@if (! $hasFilter)
    <section class="home-section service-section">
        <header class="home-section-heading">
            <div><span class="section-code">LAYANAN</span><h2>Kenapa belanja di {{ config('app.name') }}</h2></div>
        </header>
        <div class="service-strip">
            @foreach ($trustItems as $index => $item)
                <div>
                    <b>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</b>
                    <span><strong>{{ $item['title'] }}</strong><small>{{ $item['text'] }}</small></span>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
