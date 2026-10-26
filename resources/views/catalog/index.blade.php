@extends('layouts.app')

@section('title', config('app.name').' — Toko Gaming, Diecast & Hobi')

@section('content')
    @php
        $isSearching = filled(request('q'));
        $activeCategory = filled(request('category')) ? $categories->firstWhere('slug', request('category')) : null;
        $sortOptions = [
            'featured' => 'Unggulan',
            'latest' => 'Terbaru',
            'price_asc' => 'Harga terendah',
            'price_desc' => 'Harga tertinggi',
        ];
        $currentSort = request('sort', 'featured');
        $baseQuery = array_filter(['q' => request('q'), 'category' => request('category')]);
        $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
    @endphp

    @if ($isSearching || $activeCategory)
        {{-- Mode hasil: pencarian atau kategori, fokus ke produk --}}
        <section id="produk" class="container-page pt-10">
            <p class="text-sm text-ink-500">{{ $isSearching ? 'Hasil pencarian' : 'Kategori' }}</p>
            <div class="mt-1 flex flex-wrap items-end justify-between gap-4">
                <h1 class="text-3xl font-semibold tracking-tight text-ink-900 sm:text-4xl">
                    {{ $isSearching ? '“'.request('q').'”' : $activeCategory->name }}
                </h1>
                <a href="{{ route('home') }}" class="text-sm font-medium text-brand-600 hover:underline">Hapus filter</a>
            </div>
            <p class="mt-2 text-sm text-ink-500">{{ $products->total() }} produk</p>

            <div class="no-scrollbar -mx-4 mt-6 flex gap-2 overflow-x-auto px-4 md:mx-0 md:px-0">
                @foreach ($categories as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}"
                       @class([
                           'shrink-0 rounded-full px-4 py-2 text-sm transition-colors',
                           'bg-ink-900 text-white' => $activeCategory?->is($category),
                           'bg-ink-50 text-ink-700 hover:bg-ink-100' => ! $activeCategory?->is($category),
                       ])>{{ $category->name }}</a>
                @endforeach
            </div>

            @include('catalog.partials.product-grid')
        </section>
    @else
        {{-- Banner berjalan: slide bergeser otomatis, berhenti saat disentuh/di-hover --}}
        @if (count($heroSlides) > 0)
        <section class="overflow-hidden bg-ink-50" data-carousel data-interval="6000"
                 aria-roledescription="carousel" aria-label="Banner utama">
            <div class="container-page relative py-10 sm:py-14 lg:py-16">
                <div data-carousel-viewport class="touch-pan-y overflow-x-clip">
                    <div data-carousel-track class="flex gap-x-16 transition-transform duration-700 ease-(--ease-in-out) motion-reduce:duration-0">
                        @foreach ($heroSlides as $slide)
                            @php $product = $slide['product']; @endphp
                            <div data-carousel-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ count($heroSlides) }}"
                                 class="grid min-w-full items-center gap-8 lg:grid-cols-[1fr_1.1fr] lg:gap-10">
                                <div class="lg:pr-6">
                                    <p class="text-sm font-medium {{ $slide['tone'] }}">{{ $slide['eyebrow'] }}</p>
                                    <h{{ $loop->first ? '1' : '2' }} class="mt-3 text-[2.6rem] font-semibold leading-[1.04] tracking-tight text-ink-900 sm:text-6xl">
                                        {{ $slide['title'] }}<br>
                                        <span class="text-ink-400">{{ $slide['subtitle'] }}</span>
                                    </h{{ $loop->first ? '1' : '2' }}>
                                    <p class="mt-5 max-w-md text-base leading-relaxed text-ink-500 sm:text-lg">{{ $slide['body'] }}</p>
                                    <div class="mt-8 flex flex-wrap items-center gap-2">
                                        <a href="{{ $slide['cta'][1] }}" class="press rounded-full bg-ink-900 px-6 py-3 text-sm font-medium text-white hover:bg-ink-700">{{ $slide['cta'][0] }}</a>
                                        <a href="{{ $slide['secondary'][1] }}" class="rounded-full px-5 py-3 text-sm font-medium text-brand-600 hover:underline">{{ $slide['secondary'][0] }} &rsaquo;</a>
                                    </div>
                                </div>

                                <a href="{{ route('products.show', $product) }}" class="group relative block" tabindex="-1">
                                    <div class="relative aspect-[4/3] overflow-hidden rounded-[2rem] bg-white shadow-card">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" onerror="this.remove()"
                                             @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                             class="h-full w-full object-contain p-[8%] transition-[scale] duration-700 ease-(--ease-out) group-hover:scale-[1.03]">
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-4 rounded-2xl bg-white/85 px-4 py-3 shadow-card backdrop-blur-xl sm:left-5 sm:right-auto sm:min-w-72">
                                        <div class="min-w-0">
                                            <p class="text-xs text-ink-500">{{ $slide['tag'] }}</p>
                                            <p class="truncate text-sm font-medium text-ink-900">{{ $product->name }}</p>
                                        </div>
                                        <p class="shrink-0 text-right text-sm font-semibold tabular-nums text-ink-900">
                                            {{ $rupiah($product->price) }}
                                            @if ($product->hasDiscount())
                                                <span class="block text-[11px] font-medium text-danger-600">-{{ $product->discountPercent() }}%</span>
                                            @endif
                                        </p>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (count($heroSlides) > 1)
                    <div class="mt-8 flex items-center justify-between gap-4 lg:mt-10">
                        {{-- Titik indikator; yang aktif memanjang dan terisi sesuai waktu slide --}}
                        <div class="flex items-center gap-2" role="tablist" aria-label="Pilih banner">
                            @foreach ($heroSlides as $slide)
                                <button type="button" data-carousel-dot role="tab" aria-label="Banner {{ $loop->iteration }}"
                                        class="carousel-dot relative h-2 w-2 overflow-hidden rounded-full bg-ink-200">
                                    <span class="carousel-dot-fill absolute inset-0 origin-left rounded-full bg-ink-900"></span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" data-carousel-prev aria-label="Banner sebelumnya"
                                    class="grid h-10 w-10 place-items-center rounded-full bg-white text-ink-900 shadow-xs hover:bg-ink-100">
                                <svg class="h-4.5 w-4.5" aria-hidden="true"><use href="#i-arrow-left"/></svg>
                            </button>
                            <button type="button" data-carousel-next aria-label="Banner berikutnya"
                                    class="grid h-10 w-10 place-items-center rounded-full bg-white text-ink-900 shadow-xs hover:bg-ink-100">
                                <svg class="h-4.5 w-4.5" aria-hidden="true"><use href="#i-arrow-right"/></svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @endif

        {{-- Kategori --}}
        <section class="container-page mt-16" data-reveal>
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">Belanja per kategori</h2>
                    <p class="mt-1 text-sm text-ink-500">Dari gear gaming sampai koleksi diecast.</p>
                </div>
                <a href="{{ route('categories.index') }}" class="shrink-0 text-sm font-medium text-brand-600 hover:underline">Semua &rsaquo;</a>
            </div>

            <div class="no-scrollbar -mx-4 flex snap-x scroll-px-4 gap-4 overflow-x-auto px-4 pb-1 md:mx-0 md:grid md:grid-cols-4 md:overflow-visible md:px-0 lg:grid-cols-8">
                @foreach ($categories as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}" class="group w-28 shrink-0 snap-start text-center md:w-auto">
                        <span class="relative grid aspect-square place-items-center overflow-hidden rounded-2xl bg-ink-50 text-ink-400">
                            <svg class="absolute h-8 w-8" aria-hidden="true"><use href="#{{ $category->icon() }}"/></svg>
                            @if ($cover = $categoryCovers[$category->id] ?? null)
                                <img src="{{ $cover }}" alt="" loading="lazy" onerror="this.remove()"
                                     class="relative h-full w-full object-contain p-[14%] mix-blend-multiply transition-[scale] duration-500 ease-(--ease-out) group-hover:scale-[1.06]">
                            @endif
                        </span>
                        <span class="mt-3 block text-sm font-medium leading-tight text-ink-900">{{ $category->name }}</span>
                        <span class="mt-0.5 block text-xs text-ink-500">{{ $category->products_count }} produk</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Flash sale harian: hitung mundur ke tengah malam WIB (waktu server), stok asli --}}
        @if ($flashSaleProducts->isNotEmpty())
            <section id="flash-sale" class="container-page mt-20 scroll-mt-24" data-reveal>
                <div class="rounded-[2rem] bg-ink-900 p-5 text-white sm:p-8">
                    <div class="flex flex-wrap items-end justify-between gap-5">
                        <div>
                            <p class="inline-flex items-center gap-1.5 text-sm font-medium text-accent-400">
                                <svg class="h-4 w-4" aria-hidden="true"><use href="#i-bolt"/></svg>
                                Flash sale hari ini
                            </p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Diskon terbaik, berganti tiap tengah malam.</h2>
                        </div>

                        <div class="flex items-center gap-4">
                            <div data-countdown data-countdown-end="{{ $flashSaleEndsAt->getTimestampMs() }}" data-countdown-now="{{ now()->getTimestampMs() }}"
                                 role="timer" aria-label="Sisa waktu flash sale">
                                <p class="text-xs text-white/50">Berakhir dalam</p>
                                <div class="mt-1 flex items-center gap-1.5 font-semibold tabular-nums">
                                    @php $left = now()->diff($flashSaleEndsAt); @endphp
                                    <span data-countdown-hours class="grid h-10 min-w-11 place-items-center rounded-xl bg-white/10 px-2 text-lg">{{ str_pad($left->h, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-white/40">:</span>
                                    <span data-countdown-minutes class="grid h-10 min-w-11 place-items-center rounded-xl bg-white/10 px-2 text-lg">{{ str_pad($left->i, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-white/40">:</span>
                                    <span data-countdown-seconds class="grid h-10 min-w-11 place-items-center rounded-xl bg-white/10 px-2 text-lg">{{ str_pad($left->s, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('promo') }}" class="hidden shrink-0 self-end rounded-full bg-white px-4 py-2.5 text-sm font-medium text-ink-900 hover:bg-ink-100 sm:inline-block">Lihat semua</a>
                        </div>
                    </div>

                    <div class="no-scrollbar -mx-5 mt-8 flex snap-x scroll-px-5 gap-3 overflow-x-auto px-5 pb-1 sm:-mx-8 sm:scroll-px-8 sm:px-8 lg:mx-0 lg:grid lg:grid-cols-6 lg:overflow-visible lg:px-0">
                        @foreach ($flashSaleProducts as $product)
                            @php
                                $stockShare = min(100, max(6, (int) round($product->stock / 20 * 100)));
                                $lowStock = $product->stock <= 5;
                            @endphp
                            <a href="{{ route('products.show', $product) }}"
                               class="group flex w-44 shrink-0 snap-start flex-col rounded-2xl bg-white p-3 text-ink-900 sm:w-48 lg:w-auto">
                                <span class="relative block aspect-square overflow-hidden rounded-xl bg-ink-50">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()"
                                             class="h-full w-full object-contain p-[10%] mix-blend-multiply transition-[scale] duration-500 ease-(--ease-out) group-hover:scale-[1.05]">
                                    @endif
                                    <span class="absolute left-2 top-2 rounded-full bg-danger-600 px-2 py-0.5 text-[11px] font-semibold text-white">-{{ $product->discountPercent() }}%</span>
                                </span>
                                <span class="mt-3 line-clamp-2 min-h-[2lh] text-xs font-medium leading-snug">{{ $product->name }}</span>
                                <span class="mt-1.5 text-[15px] font-semibold tabular-nums">{{ $rupiah($product->price) }}</span>
                                <span class="text-[11px] tabular-nums text-ink-400 line-through">{{ $rupiah($product->compare_at_price) }}</span>
                                <span class="mt-3 block h-1.5 overflow-hidden rounded-full bg-ink-100" aria-hidden="true">
                                    <span @class(['block h-full rounded-full', 'bg-danger-500' => $lowStock, 'bg-ink-900' => ! $lowStock]) style="width: {{ $stockShare }}%"></span>
                                </span>
                                <span @class(['mt-1.5 text-[11px]', 'font-medium text-danger-600' => $lowStock, 'text-ink-500' => ! $lowStock])>
                                    {{ $lowStock ? 'Sisa '.$product->stock.' unit' : 'Stok tersedia' }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <a href="{{ route('promo') }}" class="mt-6 block rounded-full bg-white py-2.5 text-center text-sm font-medium text-ink-900 sm:hidden">Lihat semua promo</a>
                </div>
            </section>
        @endif

        {{-- Pilihan editor: kartu besar ala galeri --}}
        @if ($featuredProducts->isNotEmpty())
            <section class="container-page mt-20" data-reveal>
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">Pilihan editor</h2>
                    <p class="mt-1 text-sm text-ink-500">Produk yang paling sering kami rekomendasikan.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ($featuredProducts as $product)
                        <a href="{{ route('products.show', $product) }}"
                           class="group relative flex min-h-80 flex-col overflow-hidden rounded-[1.75rem] bg-ink-50 p-7 sm:min-h-96 sm:p-9">
                            <div class="relative z-10 max-w-[60%]">
                                <p class="text-xs font-medium text-ink-500">{{ $product->category->name }}</p>
                                <h3 class="mt-2 text-xl font-semibold leading-tight text-ink-900 sm:text-2xl">{{ $product->name }}</h3>
                                <p class="mt-2 text-sm tabular-nums text-ink-700">
                                    {{ $rupiah($product->price) }}
                                    @if ($product->hasDiscount())
                                        <span class="ml-1 text-ink-400 line-through">{{ $rupiah($product->compare_at_price) }}</span>
                                    @endif
                                </p>
                                <span class="mt-4 inline-block text-sm font-medium text-brand-600 group-hover:underline">Lihat produk &rsaquo;</span>
                            </div>
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()"
                                     class="absolute bottom-0 right-0 h-[72%] w-[62%] object-contain object-bottom p-6 mix-blend-multiply transition-[scale] duration-700 ease-(--ease-out) group-hover:scale-[1.04]">
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Voucher --}}
        @if ($vouchers->isNotEmpty())
            <section id="vouchers" class="container-page mt-20" data-reveal>
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">Voucher toko</h2>
                    <p class="mt-1 text-sm text-ink-500">Salin kode, lalu pakai saat checkout.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($vouchers as $voucher)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-ink-200 p-5">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-semibold tracking-wide text-ink-900">{{ $voucher->code }}</p>
                                <p class="mt-1 text-sm text-ink-700">
                                    {{ $voucher->type === 'percent' ? 'Diskon '.$voucher->value.'%' : 'Potongan '.$rupiah($voucher->value) }}
                                </p>
                                <p class="mt-0.5 truncate text-xs text-ink-500">
                                    {{ $voucher->min_spend > 0 ? 'Min. belanja '.$rupiah($voucher->min_spend) : 'Tanpa minimum belanja' }}
                                </p>
                            </div>
                            <button type="button" data-copy-voucher="{{ $voucher->code }}"
                                    class="shrink-0 rounded-full bg-ink-50 px-3.5 py-2 text-xs font-medium text-ink-900 hover:bg-ink-100">
                                Salin
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Brand resmi --}}
        <section class="container-page mt-20" data-reveal>
            <p class="text-center text-xs font-medium uppercase tracking-[0.18em] text-ink-400">Brand resmi</p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-4">
                @foreach (['PlayStation', 'ASUS ROG', 'Logitech G', 'HyperX', 'Secretlab', 'Mini GT', 'MSI', 'Hot Wheels'] as $brand)
                    <a href="{{ route('home', ['q' => explode(' ', $brand)[0]]) }}"
                       class="text-lg font-semibold tracking-tight text-ink-300 transition-colors hover:text-ink-900">{{ $brand }}</a>
                @endforeach
            </div>
        </section>

        {{-- Semua produk --}}
        <section id="produk" class="container-page mt-20 scroll-mt-24" data-reveal>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">Semua produk</h2>
                    <p class="mt-1 text-sm text-ink-500">{{ $products->total() }} produk tersedia</p>
                </div>
            </div>

            @include('catalog.partials.product-grid')
        </section>
    @endif
@endsection
