@extends('layouts.app')

@section('title', config('app.name').' — Toko Gaming, Diecast & Hobi')

@section('content')
    <div class="container-page pt-6">
        <div class="grid gap-4 lg:grid-cols-[240px_1fr]">
            {{-- Sidebar kategori (desktop) --}}
            <aside class="hidden lg:block">
                <div class="sticky top-32 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
                    <div class="flex items-center gap-2 border-b border-ink-100 px-4 py-3">
                        <svg class="h-4.5 w-4.5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-grid"/></svg>
                        <span class="text-sm font-bold text-ink-900">Semua Kategori</span>
                    </div>
                    <nav class="p-1.5">
                        @foreach ($categories as $category)
                            <a href="{{ route('home', ['category' => $category->slug]) }}"
                               class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-ink-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <span class="truncate">{{ $category->name }}</span>
                                <span class="ml-2 shrink-0 rounded-md bg-ink-100 px-1.5 py-0.5 text-[11px] font-semibold text-ink-500">{{ $category->products_count }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- Hero --}}
            <div class="space-y-4">
                <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-6 text-white sm:p-9">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-500/25 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-28 left-1/4 h-64 w-64 rounded-full bg-accent-500/15 blur-3xl"></div>

                    <div class="relative grid items-center gap-8 lg:grid-cols-2">
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/90 ring-1 ring-white/15">
                                <svg class="h-3.5 w-3.5 text-accent-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-flame"/></svg>
                                Gear baru tiap minggu
                            </span>
                            <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">
                                Lengkapi Setup <span class="text-brand-300">Gaming</span> &amp; Koleksi <span class="text-accent-400">Diecast</span> Kamu
                            </h1>
                            <p class="mt-3 max-w-md text-sm leading-relaxed text-white/70">
                                Keyboard mekanikal, headset, komponen PC, sampai diecast skala 1:18. Semua pilihan untuk main, kerja, dan hobi.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="#produk" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-500">
                                    Belanja sekarang
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><use href="#i-arrow-right"/></svg>
                                </a>
                                <a href="{{ route('promo') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                                    Lihat promo
                                </a>
                            </div>

                            <dl class="mt-7 flex flex-wrap gap-6">
                                <div>
                                    <dt class="text-xs text-white/50">Produk pilihan</dt>
                                    <dd class="text-xl font-extrabold">{{ $products->total() }}+</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-white/50">Kategori</dt>
                                    <dd class="text-xl font-extrabold">{{ $categories->count() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-white/50">Gratis ongkir</dt>
                                    <dd class="text-xl font-extrabold">Rp300rb+</dd>
                                </div>
                            </dl>
                        </div>

                        @if ($heroProduct)
                            <a href="{{ route('products.show', $heroProduct) }}"
                               class="group relative block overflow-hidden rounded-2xl bg-white/5 ring-1 ring-white/10 transition hover:ring-brand-400/50">
                                <img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}"
                                     class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105">
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-950 via-ink-950/70 to-transparent p-5">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-brand-300">{{ $heroProduct->category->name }}</span>
                                    <h3 class="mt-1 text-base font-bold text-white">{{ $heroProduct->name }}</h3>
                                    <p class="mt-1 text-lg font-extrabold text-accent-400">Rp{{ number_format($heroProduct->price, 0, ',', '.') }}</p>
                                </div>
                                @if ($heroProduct->hasDiscount())
                                    <span class="absolute right-4 top-4 rounded-lg bg-danger-500 px-2.5 py-1.5 text-xs font-bold text-white">
                                        -{{ $heroProduct->discountPercent() }}%
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </section>

                {{-- Bar keunggulan --}}
                <section class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        ['i-truck', 'Gratis ongkir', 'Belanja min. Rp300.000'],
                        ['i-shield', 'Garansi resmi', 'Produk original bergaransi'],
                        ['i-refresh', 'Retur 7 hari', 'Tidak cocok? Tukar saja'],
                    ] as [$icon, $title, $desc])
                        <div class="flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-ink-900">{{ $title }}</p>
                                <p class="truncate text-xs text-ink-500">{{ $desc }}</p>
                            </div>
                        </div>
                    @endforeach
                </section>
            </div>
        </div>
    </div>

    {{-- Kategori --}}
    <section class="container-page mt-10" data-reveal>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-ink-900">Jelajahi Kategori</h2>
                <p class="mt-0.5 text-sm text-ink-500">Temukan gear sesuai kebutuhanmu.</p>
            </div>
            <a href="{{ route('categories.index') }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                Semua kategori
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}"
                   class="group flex flex-col items-center gap-2.5 rounded-2xl border border-ink-100 bg-white p-4 text-center shadow-card transition hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 text-brand-600 transition group-hover:from-brand-500 group-hover:to-brand-700 group-hover:text-white">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-gamepad"/></svg>
                    </span>
                    <span class="text-xs font-semibold leading-tight text-ink-700 group-hover:text-brand-700">{{ $category->name }}</span>
                    <span class="text-[11px] text-ink-400">{{ $category->products_count }} produk</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Produk Unggulan --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="container-page mt-10" data-reveal>
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-500/15 px-2.5 py-1 text-xs font-bold text-accent-600">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-bolt"/></svg>
                        Paling dicari
                    </span>
                    <h2 class="mt-2 text-xl font-extrabold tracking-tight text-ink-900">Produk Unggulan</h2>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-5">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Grid produk utama --}}
    <section id="produk" class="container-page mt-12" data-reveal>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-ink-900">
                    {{ request('q') ? 'Hasil pencarian "'.request('q').'"' : 'Katalog Produk' }}
                </h2>
                <p class="mt-0.5 text-sm text-ink-500">{{ $products->total() }} produk ditemukan</p>
            </div>

            @if (request('q') || request('category'))
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-ink-200 bg-white px-3.5 py-2 text-sm font-semibold text-ink-600 transition hover:border-ink-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-close"/></svg>
                    Reset filter
                </a>
            @endif
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
@endsection
