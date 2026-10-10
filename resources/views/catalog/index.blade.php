@extends('layouts.app')

@section('title', config('app.name').' — Toko Gaming, Diecast & Hobi')

@section('content')
    @php
        $isSearching = filled(request('q'));
        $activeCategory = filled(request('category'));
    @endphp

    @if ($isSearching)
        {{-- Mode pencarian: tampilkan hasil fokus tanpa hero/kategori besar --}}
        <section id="produk" class="container-page pt-6">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm text-ink-500">Hasil pencarian untuk</p>
                    <h1 class="mt-0.5 text-2xl font-extrabold tracking-tight text-ink-900">"{{ request('q') }}"</h1>
                    <p class="mt-1 text-sm text-ink-500">{{ $products->total() }} produk ditemukan</p>
                </div>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-ink-200 bg-white px-3.5 py-2 text-xs font-bold text-ink-600 transition hover:border-ink-300 sm:text-sm">
                    <svg class="h-4 w-4"><use href="#i-close"/></svg>
                    Reset Pencarian
                </a>
            </div>

            @if ($products->isNotEmpty())
                @php
                    $searchSortOptions = [
                        'featured' => 'Paling Cocok',
                        'latest' => 'Terbaru',
                        'price_asc' => 'Harga Terendah',
                        'price_desc' => 'Harga Tertinggi',
                    ];
                    $currentSearchSort = request('sort', 'featured');
                @endphp
                <div class="no-scrollbar mb-4 flex items-center gap-2 overflow-x-auto">
                    <span class="shrink-0 text-xs font-bold text-ink-600">Urutkan:</span>
                    @foreach ($searchSortOptions as $key => $label)
                        <a href="{{ route('home', ['q' => request('q'), 'sort' => $key]) }}"
                           @class([
                               'shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition',
                               'border-brand-500 bg-brand-600 text-white shadow-sm' => $currentSearchSort === $key,
                               'border-ink-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' => $currentSearchSort !== $key,
                           ])>{{ $label }}</a>
                    @endforeach
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
                    <p class="mt-3 font-semibold text-ink-700">Produk tidak ditemukan</p>
                    <p class="mt-1 text-sm text-ink-500">Coba kata kunci lain, periksa ejaan, atau jelajahi kategori.</p>
                </div>

                {{-- Saran kategori populer --}}
                @if ($categories->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-sm font-bold text-ink-900">Jelajahi kategori</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($categories as $category)
                                <a href="{{ route('home', ['category' => $category->slug]) }}"
                                   class="rounded-full border border-ink-200 bg-white px-3.5 py-2 text-sm font-medium text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                                    {{ $category->name }}
                                    <span class="ml-1 text-xs text-ink-400">{{ $category->products_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </section>
    @else
    <div class="container-page pt-5">
        {{-- Banner Tokopedia Style: Main Slider + 2 Side Promo Banners --}}
        <div class="grid gap-3.5 lg:grid-cols-[1fr_320px]">
            {{-- Carousel Banner Utama --}}
            <div data-carousel class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-950 via-brand-950 to-ink-900 shadow-lift">
                <div data-carousel-track class="flex transition-transform duration-500 ease-out">
                    {{-- Slide 1: Setup Gaming --}}
                    <div data-carousel-slide class="relative min-w-full p-6 text-white sm:p-9">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl"></div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-500/20 px-3 py-1 text-xs font-bold text-brand-300 ring-1 ring-brand-400/30">
                            <svg class="h-3.5 w-3.5 text-accent-400"><use href="#i-bolt"/></svg>
                            Official Gaming Fest 2026
                        </span>
                        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-4xl">
                            Setup Impian <span class="text-brand-300">Gamers</span> &amp; Profesional
                        </h2>
                        <p class="mt-2 max-w-lg text-xs leading-relaxed text-white/75 sm:text-sm">
                            Konsol PS5, keyboard mekanikal hot-swap, dan audio berkelas turnamen. Garansi resmi 100% original.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            <a href="#flash-sale" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-brand-500 sm:text-sm">
                                Cek Kejar Diskon
                                <svg class="h-4 w-4"><use href="#i-arrow-right"/></svg>
                            </a>
                            <a href="{{ route('promo') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20 sm:text-sm">
                                Semua Promo
                            </a>
                        </div>
                    </div>

                    {{-- Slide 2: Diecast Collector --}}
                    <div data-carousel-slide class="relative min-w-full p-6 text-white sm:p-9">
                        <div class="pointer-events-none absolute -right-20 -bottom-20 h-64 w-64 rounded-full bg-amber-500/20 blur-3xl"></div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-3 py-1 text-xs font-bold text-accent-300 ring-1 ring-amber-400/30">
                            <svg class="h-3.5 w-3.5 text-accent-400"><use href="#i-flame"/></svg>
                            Diecast &amp; Miniatur Koleksi
                        </span>
                        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-4xl">
                            Mini GT &amp; Hot Wheels <span class="text-accent-400">Limited Run</span>
                        </h2>
                        <p class="mt-2 max-w-lg text-xs leading-relaxed text-white/75 sm:text-sm">
                            Skala presisi 1:64 Nissan Skyline R34 LBWK, Porsche 911, dan Toyota AE86. Stok kolektor terbatas!
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            <a href="{{ route('home', ['q' => 'Diecast']) }}" class="inline-flex items-center gap-2 rounded-xl bg-accent-500 px-4 py-2.5 text-xs font-bold text-ink-950 transition hover:bg-accent-400 sm:text-sm">
                                Koleksi Sekarang
                                <svg class="h-4 w-4"><use href="#i-arrow-right"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Slide 3: PC Components --}}
                    <div data-carousel-slide class="relative min-w-full p-6 text-white sm:p-9">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-purple-500/25 blur-3xl"></div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-500/20 px-3 py-1 text-xs font-bold text-purple-300 ring-1 ring-purple-400/30">
                            <svg class="h-3.5 w-3.5"><use href="#i-sparkles"/></svg>
                            Komponen Next-Gen
                        </span>
                        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-4xl">
                            GeForce <span class="text-emerald-400">RTX Series</span> &amp; DDR5 RAM
                        </h2>
                        <p class="mt-2 max-w-lg text-xs leading-relaxed text-white/75 sm:text-sm">
                            Kartu grafis RTX 5060 Ti hingga RTX 5090 dan prosesor terbaru. Siap libas resolusi 4K tanpa kompromi.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            <a href="{{ route('home', ['q' => 'Komponen PC']) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-500 sm:text-sm">
                                Rakit Sekarang
                                <svg class="h-4 w-4"><use href="#i-arrow-right"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Kontrol navigasi carousel --}}
                <div class="absolute bottom-4 right-4 flex items-center gap-2">
                    <button type="button" data-carousel-prev class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur transition hover:bg-white/40" aria-label="Slide sebelumnya">
                        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg>
                    </button>
                    <button type="button" data-carousel-next class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur transition hover:bg-white/40" aria-label="Slide berikutnya">
                        <svg class="h-4 w-4"><use href="#i-arrow-right"/></svg>
                    </button>
                    <div class="ml-2 flex items-center gap-1.5">
                        <button type="button" data-carousel-dot class="h-2 w-2 rounded-full bg-white transition"></button>
                        <button type="button" data-carousel-dot class="h-2 w-2 rounded-full bg-white/40 transition"></button>
                        <button type="button" data-carousel-dot class="h-2 w-2 rounded-full bg-white/40 transition"></button>
                    </div>
                </div>
            </div>

            {{-- 2 Side Banners (Khas Tokopedia) --}}
            <div class="hidden flex-col gap-3.5 lg:flex">
                <a href="{{ route('promo') }}" class="group relative flex flex-1 flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br from-red-600 via-rose-600 to-amber-600 p-5 text-white shadow-card transition hover:shadow-lift">
                    <div class="relative z-10">
                        <span class="inline-block rounded-md bg-white/20 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide">Promo Kilat</span>
                        <h3 class="mt-2 text-lg font-black leading-tight">Kejar Diskon<br>Hingga 50%</h3>
                        <p class="mt-1 text-xs text-white/80">Stok terbatas tiap hari</p>
                    </div>
                    <span class="relative z-10 inline-flex items-center gap-1 text-xs font-bold text-white group-hover:underline">
                        Lihat Produk &rarr;
                    </span>
                    <div class="absolute -right-6 -bottom-6 h-28 w-28 rounded-full bg-white/10 blur-xl"></div>
                </a>

                <a href="#vouchers" class="group relative flex flex-1 flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 via-brand-800 to-ink-900 p-5 text-white shadow-card transition hover:shadow-lift">
                    <div class="relative z-10">
                        <span class="inline-block rounded-md bg-emerald-500/30 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-emerald-300">Gratis Ongkir</span>
                        <h3 class="mt-2 text-lg font-black leading-tight">Voucher Toko<br>Klaim Sekarang</h3>
                        <p class="mt-1 text-xs text-white/80">Potongan s/d Rp250.000</p>
                    </div>
                    <span class="relative z-10 inline-flex items-center gap-1 text-xs font-bold text-white group-hover:underline">
                        Klaim Kupon &rarr;
                    </span>
                    <div class="absolute -right-6 -bottom-6 h-28 w-28 rounded-full bg-brand-400/15 blur-xl"></div>
                </a>
            </div>
        </div>

        {{-- Bar Klaim Voucher Toko (Shopee Coupon Rail Style) --}}
        @if (isset($vouchers) && $vouchers->isNotEmpty())
            <section id="vouchers" class="mt-5 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                <div class="flex items-center justify-between gap-3 border-b border-ink-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-danger-50 text-danger-600">
                            <svg class="h-4.5 w-4.5"><use href="#i-ticket"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-ink-900">Klaim Voucher Toko</h3>
                            <p class="text-[11px] text-ink-500">Salin kode dan pakai saat checkout untuk hemat lebih banyak</p>
                        </div>
                    </div>
                    <span class="hidden text-xs font-semibold text-brand-600 sm:inline-block">Gunakan di Keranjang &amp; Checkout</span>
                </div>

                <div class="no-scrollbar mt-3 flex gap-3 overflow-x-auto pb-1">
                    @foreach ($vouchers as $voucher)
                        <div class="relative flex min-w-[240px] flex-1 items-center justify-between gap-3 rounded-xl border border-dashed border-brand-200 bg-gradient-to-r from-brand-50/70 to-white p-3">
                            <div class="min-w-0">
                                <span class="rounded bg-brand-600 px-1.5 py-0.5 font-mono text-[10px] font-bold text-white">{{ $voucher->code }}</span>
                                <p class="mt-1 text-xs font-bold text-ink-900">
                                    {{ $voucher->type === 'percent' ? 'Diskon '.$voucher->value.'%' : 'Potongan Rp'.number_format($voucher->value, 0, ',', '.') }}
                                </p>
                                <p class="truncate text-[10px] text-ink-500">Min. belanja Rp{{ number_format($voucher->min_spend, 0, ',', '.') }}</p>
                            </div>
                            <button type="button" data-copy-voucher="{{ $voucher->code }}"
                                    class="shrink-0 rounded-lg border border-brand-300 bg-white px-2.5 py-1.5 text-xs font-bold text-brand-700 transition hover:bg-brand-600 hover:text-white shadow-sm">
                                Salin
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Flash Sale / Kejar Diskon Tokopedia Style --}}
        @if (isset($flashSaleProducts) && $flashSaleProducts->isNotEmpty())
            <section id="flash-sale" class="mt-6 overflow-hidden rounded-2xl border border-rose-200 bg-gradient-to-b from-rose-50/60 to-white p-4 shadow-card sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rose-100 pb-3.5">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-3 py-1.5 text-xs font-black uppercase tracking-wider text-white shadow-sm">
                            <svg class="h-4 w-4 animate-pulse"><use href="#i-flame"/></svg>
                            FLASH SALE
                        </span>

                        {{-- Countdown Timer Real-time --}}
                        <div data-flash-sale-timer class="flex items-center gap-1 text-xs font-extrabold text-ink-900">
                            <span class="text-[11px] font-semibold text-ink-500 hidden sm:inline">Berakhir Dalam:</span>
                            <span data-hours class="flex h-6 min-w-6 items-center justify-center rounded-md bg-ink-900 px-1.5 text-[11px] font-mono text-white">02</span>
                            <span class="font-bold text-ink-600">:</span>
                            <span data-minutes class="flex h-6 min-w-6 items-center justify-center rounded-md bg-ink-900 px-1.5 text-[11px] font-mono text-white">45</span>
                            <span class="font-bold text-ink-600">:</span>
                            <span data-seconds class="flex h-6 min-w-6 items-center justify-center rounded-md bg-ink-900 px-1.5 text-[11px] font-mono text-white">30</span>
                        </div>
                    </div>

                    <a href="{{ route('promo') }}" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-700">
                        Lihat Semua Promo
                        <svg class="h-3.5 w-3.5"><use href="#i-arrow-right"/></svg>
                    </a>
                </div>

                {{-- Slider / Grid Flash Sale --}}
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach ($flashSaleProducts as $product)
                        @php
                            $discount = $product->discountPercent();
                            $soldCount = max(8, ($product->id * 19) % 95);
                            $progress = min(92, max(35, ($product->id * 23) % 90));
                        @endphp
                        <div class="group flex flex-col rounded-xl border border-rose-100 bg-white p-2.5 shadow-sm transition hover:-translate-y-1 hover:border-rose-300 hover:shadow-card">
                            <div class="relative aspect-square overflow-hidden rounded-lg bg-ink-50">
                                <a href="{{ route('products.show', $product) }}" class="block h-full">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                                         class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                </a>
                                <span class="absolute left-1.5 top-1.5 rounded bg-danger-600 px-1.5 py-0.5 text-[10px] font-black text-white shadow">
                                    -{{ $discount }}%
                                </span>
                            </div>

                            <a href="{{ route('products.show', $product) }}" class="mt-2 line-clamp-1 text-xs font-semibold text-ink-900 hover:text-brand-600">
                                {{ $product->name }}
                            </a>

                            <div class="mt-1">
                                <div class="text-xs font-black text-rose-600 sm:text-sm">
                                    Rp{{ number_format($product->price, 0, ',', '.') }}
                                </div>
                                <del class="text-[10px] text-ink-400">
                                    Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}
                                </del>
                            </div>

                            {{-- Shopee Style Terjual Bar --}}
                            <div class="mt-2">
                                <div class="relative h-3.5 w-full overflow-hidden rounded-full bg-rose-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-rose-600" style="width: {{ $progress }}%"></div>
                                    <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-white drop-shadow">
                                        Terjual {{ $soldCount }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Bar keunggulan 3 pilar --}}
        <section class="mt-6 grid gap-3 sm:grid-cols-3">
            @foreach ([
                ['i-truck', 'Bebas Ongkir Seluruh Indonesia', 'Tanpa ribet belanja min. Rp300.000'],
                ['i-shield', 'Jaminan 100% Produk Original', 'Garansi resmi distributor & brand'],
                ['i-refresh', 'Retur Mudah & Garansi 7 Hari', 'Barang rusak atau salah? Tukar ganti baru'],
            ] as [$icon, $title, $desc])
                <div class="flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-3.5 shadow-card">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-5 w-5"><use href="#{{ $icon }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-ink-900 sm:text-sm">{{ $title }}</p>
                        <p class="truncate text-[11px] text-ink-500">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </section>
    </div>

    {{-- Kategori Pilihan (Tokopedia Style Icon Grid) --}}
    <section class="container-page mt-8" data-reveal>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-ink-900 sm:text-xl">Kategori Pilihan</h2>
                <p class="mt-0.5 text-xs text-ink-500 sm:text-sm">Jelajahi gear gaming, komponen, dan hobi koleksi.</p>
            </div>
            <a href="{{ route('categories.index') }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 sm:text-sm">
                Semua Kategori
                <svg class="h-4 w-4"><use href="#i-arrow-right"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}"
                   class="group flex flex-col items-center gap-2 rounded-2xl border border-ink-100 bg-white p-3.5 text-center shadow-card transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-lift">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 text-brand-600 transition group-hover:from-brand-500 group-hover:to-brand-700 group-hover:text-white">
                        <svg class="h-6 w-6"><use href="#i-gamepad"/></svg>
                    </span>
                    <span class="text-xs font-bold leading-tight text-ink-700 group-hover:text-brand-700">{{ $category->name }}</span>
                    <span class="text-[10px] text-ink-400">{{ $category->products_count }} produk</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Brand Official Partner (Tokopedia Official Store Pavilion) --}}
    <section class="container-page mt-10" data-reveal>
        <div class="mb-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-700 text-white">
                    <svg class="h-4 w-4"><use href="#i-badge-check"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-ink-900 sm:text-lg">Official Brand Partners</h2>
                    <p class="text-[11px] text-ink-500">100% Produk Original Bergaransi Resmi Distributor</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ([
                ['Sony PlayStation', 'Konsol Resmi', 'bg-blue-50 text-blue-800'],
                ['ASUS ROG', 'Gaming Hardware', 'bg-red-50 text-red-800'],
                ['Logitech G', 'Periferal Pro', 'bg-cyan-50 text-cyan-800'],
                ['HyperX', 'Audio & Headset', 'bg-rose-50 text-rose-800'],
                ['Secretlab', 'Kursi Gaming', 'bg-amber-50 text-amber-800'],
                ['Mini GT', 'Diecast 1:64', 'bg-emerald-50 text-emerald-800'],
                ['MSI Gaming', 'GPU & Monitor', 'bg-red-50 text-red-800'],
                ['Hot Wheels', 'Koleksi Premium', 'bg-orange-50 text-orange-800'],
            ] as [$brand, $sub, $badgeClass])
                <a href="{{ route('home', ['q' => explode(' ', $brand)[0]]) }}"
                   class="flex flex-col items-center justify-center rounded-xl border border-ink-100 bg-white p-3 text-center shadow-card transition hover:border-brand-300 hover:shadow-lift">
                    <span class="font-extrabold text-ink-900 text-xs sm:text-sm">{{ $brand }}</span>
                    <span class="mt-1 rounded px-1.5 py-0.5 text-[9px] font-semibold {{ $badgeClass }}">{{ $sub }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Produk Rekomendasi Unggulan --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="container-page mt-10" data-reveal>
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-500/15 px-2.5 py-1 text-xs font-bold text-accent-600">
                        <svg class="h-3.5 w-3.5"><use href="#i-bolt"/></svg>
                        Paling Banyak Dicari
                    </span>
                    <h2 class="mt-2 text-lg font-extrabold tracking-tight text-ink-900 sm:text-xl">Rekomendasi Unggulan</h2>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Grid produk utama --}}
    <section id="produk" class="container-page mt-12" data-reveal>
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 pb-3 text-xs text-ink-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition hover:text-brand-600">Beranda</a>
            <span class="text-ink-200">&rsaquo;</span>
            @if (request('q'))
                <span class="font-medium text-ink-700">Hasil Pencarian "{{ request('q') }}"</span>
            @elseif (request('category'))
                <span class="font-medium text-ink-700">{{ $categories->firstWhere('slug', request('category'))?->name ?? 'Kategori' }}</span>
            @else
                <span class="font-medium text-ink-700">Katalog Produk</span>
            @endif
        </nav>

        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-ink-900 sm:text-xl">
                    {{ request('q') ? 'Hasil Pencarian "' . request('q') . '"' : 'Katalog Produk' }}
                </h2>
                <p class="mt-0.5 text-xs text-ink-500 sm:text-sm">{{ $products->total() }} produk ditemukan</p>
            </div>

            @if (request('q') || request('category'))
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-ink-200 bg-white px-3.5 py-2 text-xs font-bold text-ink-600 transition hover:border-ink-300 sm:text-sm">
                    <svg class="h-4 w-4"><use href="#i-close"/></svg>
                    Reset Filter
                </a>
            @endif
        </div>

        {{-- Sortir cepat (Tokopedia / Shopee style) --}}
        @php
            $sortOptions = [
                'featured' => 'Paling Cocok',
                'latest' => 'Terbaru',
                'price_asc' => 'Harga Terendah',
                'price_desc' => 'Harga Tertinggi',
            ];
            $currentSort = request('sort', 'featured');
            $baseQuery = array_filter(['q' => request('q'), 'category' => request('category')]);
        @endphp
        <div class="no-scrollbar mb-4 flex items-center gap-2 overflow-x-auto">
            <span class="shrink-0 text-xs font-bold text-ink-600">Urutkan:</span>
            @foreach ($sortOptions as $key => $label)
                <a href="{{ route('home', array_merge($baseQuery, ['sort' => $key])) }}#produk"
                   @class([
                       'shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition',
                       'border-brand-500 bg-brand-600 text-white shadow-sm' => $currentSort === $key,
                       'border-ink-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' => $currentSort !== $key,
                   ])>{{ $label }}</a>
            @endforeach
        </div>

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
                <p class="mt-3 font-semibold text-ink-700">Produk tidak ditemukan</p>
                <p class="mt-1 text-sm text-ink-500">Coba kata kunci lain atau jelajahi kategori.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Kembali ke beranda</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </section>
    @endif
@endsection
