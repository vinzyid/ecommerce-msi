<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name') }}, toko online perlengkapan gaming, diecast, dan hobi koleksi.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name').' — Perlengkapan Gaming, Diecast & Hobi')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.icons')
</head>
<body class="min-h-screen bg-[#f4f6fb] text-ink-900 antialiased @yield('body-class')">

{{-- Top Utility Bar (khas Tokopedia & Shopee) --}}
<div class="hidden border-b border-ink-100 bg-ink-50/90 text-[11px] font-medium text-ink-500 md:block">
    <div class="container-page flex h-8 items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="inline-flex items-center gap-1.5 transition hover:text-brand-600">
                <svg class="h-3.5 w-3.5 text-brand-600"><use href="#i-headset"/></svg>
                Download VinzyPlay App
            </span>
            <span class="text-ink-200">|</span>
            <span class="transition hover:text-brand-600">Mitra Seller</span>
            <span class="text-ink-200">|</span>
            <a href="{{ route('about') }}" class="transition hover:text-brand-600">Tentang VinzyPlay</a>
        </div>
        <div class="flex items-center gap-4">
            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600">
                <svg class="h-3.5 w-3.5"><use href="#i-truck"/></svg>
                Bebas Ongkir Seluruh Indonesia
            </span>
            <span class="text-ink-200">|</span>
            <a href="{{ route('about') }}" class="transition hover:text-brand-600">Bantuan &amp; CS 24/7</a>
        </div>
    </div>
</div>

<header class="sticky top-0 z-40 border-b border-ink-100 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="container-page">
        <div class="flex h-16 items-center gap-3 md:gap-5">
            <button type="button" data-mobile-toggle
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-ink-600 hover:bg-ink-50 lg:hidden"
                    aria-label="Buka menu" aria-expanded="false">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-menu"/></svg>
            </button>

            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2" aria-label="{{ config('app.name') }}, halaman utama">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-black text-white shadow-sm">N</span>
                <span class="hidden text-lg font-extrabold tracking-tight text-ink-900 sm:block">Vinzy<span class="text-brand-600">Play</span></span>
            </a>

            <div class="relative hidden flex-1 md:block">
                <form method="GET" action="{{ route('home') }}" role="search" class="relative">
                    <label class="sr-only" for="site-search">Cari produk</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
                    <input id="site-search" name="q" type="search"
                           value="{{ request('q') }}"
                           placeholder="Cari keyboard mechanical, diecast, headset gaming..."
                           class="w-full rounded-xl border border-ink-200 bg-ink-50 py-2.5 pl-10 pr-24 text-sm outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20">
                    <button type="submit"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                        Cari
                    </button>
                </form>
                <div class="mt-1 flex items-center gap-2 overflow-hidden text-[11px] text-ink-400">
                    <span class="shrink-0 font-semibold text-ink-500">Populer:</span>
                    <a href="{{ route('home', ['q' => 'PlayStation 5']) }}" class="truncate transition hover:text-brand-600">PlayStation 5</a>
                    <span class="text-ink-200">&bull;</span>
                    <a href="{{ route('home', ['q' => 'RTX 5060']) }}" class="truncate transition hover:text-brand-600">RTX 5060 Ti</a>
                    <span class="text-ink-200">&bull;</span>
                    <a href="{{ route('home', ['q' => 'Mechanical Keyboard']) }}" class="truncate transition hover:text-brand-600">Neo75 Keyboard</a>
                    <span class="text-ink-200">&bull;</span>
                    <a href="{{ route('home', ['q' => 'Hot Wheels']) }}" class="truncate transition hover:text-brand-600">Hot Wheels</a>
                    <span class="text-ink-200">&bull;</span>
                    <a href="{{ route('home', ['q' => 'HyperX']) }}" class="truncate transition hover:text-brand-600">HyperX</a>
                </div>
            </div>

            <div class="ml-auto flex items-center gap-1.5">
                @auth
                    <a href="{{ route('wishlist.index') }}"
                       class="relative hidden h-10 w-10 items-center justify-center rounded-xl text-ink-600 hover:bg-ink-50 sm:flex" aria-label="Wishlist">
                        <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-heart"/></svg>
                        @if (($headerWishlistCount = auth()->user()->wishlists()->count()) > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-danger-500 px-1 text-[10px] font-bold text-white">{{ $headerWishlistCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('cart.index') }}"
                       class="relative flex h-10 w-10 items-center justify-center rounded-xl text-ink-600 hover:bg-ink-50" aria-label="Keranjang">
                        <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-cart"/></svg>
                        @if (($headerCartCount = auth()->user()->cartItems()->sum('quantity')) > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-brand-600 px-1 text-[10px] font-bold text-white">{{ $headerCartCount }}</span>
                        @endif
                    </a>

                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger aria-haspopup="true" aria-expanded="false"
                                class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-sm font-semibold text-ink-700 hover:bg-ink-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ink-900 text-xs font-bold text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                            <span class="hidden max-w-24 truncate lg:block">{{ auth()->user()->username }}</span>
                            <svg class="hidden h-4 w-4 text-ink-400 lg:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-chevron"/></svg>
                        </button>
                        <div data-dropdown-menu
                             class="invisible absolute right-0 top-full z-50 mt-2 w-56 origin-top-right scale-95 rounded-2xl border border-ink-100 bg-white p-1.5 opacity-0 shadow-lift transition duration-150">
                            <a href="{{ route('account') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-700 hover:bg-ink-50">
                                <svg class="h-4.5 w-4.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-user"/></svg> Akun saya
                            </a>
                            <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-700 hover:bg-ink-50">
                                <svg class="h-4.5 w-4.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-receipt"/></svg> Pesanan
                            </a>
                            <a href="{{ route('wishlist.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-700 hover:bg-ink-50">
                                <svg class="h-4.5 w-4.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-heart"/></svg> Wishlist
                            </a>
                            @if (auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-700 hover:bg-ink-50">
                                    <svg class="h-4.5 w-4.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-shield"/></svg> Panel admin
                                </a>
                            @endif
                            <div class="my-1.5 h-px bg-ink-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold text-danger-600 hover:bg-danger-500/10">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-logout"/></svg> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-50">Masuk</a>
                    <a href="{{ route('register') }}"
                       class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Daftar</a>
                @endauth
            </div>
        </div>

        <form method="GET" action="{{ route('home') }}" role="search" class="pb-3 md:hidden">
            <label class="sr-only" for="site-search-mobile">Cari produk</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="site-search-mobile" name="q" type="search" value="{{ request('q') }}"
                       placeholder="Cari produk gaming, diecast, hobi..."
                       class="w-full rounded-xl border border-ink-200 bg-ink-50 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20">
            </div>
        </form>
    </div>

    <nav class="hidden border-t border-ink-100 lg:block" aria-label="Navigasi utama">
        <div class="container-page flex h-11 items-center gap-6 text-sm font-semibold text-ink-600">
            <a href="{{ route('home') }}" @class(['text-brand-700' => request()->routeIs('home'), 'hover:text-brand-700' => !request()->routeIs('home'), 'transition'])>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-store"/></svg> Beranda
                </span>
            </a>
            <a href="{{ route('categories.index') }}" @class(['text-brand-700' => request()->routeIs('categories.index', 'products.show'), 'hover:text-brand-700' => !request()->routeIs('categories.index', 'products.show'), 'transition'])>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-grid"/></svg> Kategori
                </span>
            </a>
            <a href="{{ route('promo') }}" @class(['text-danger-600' => request()->routeIs('promo'), 'hover:text-danger-600' => !request()->routeIs('promo'), 'transition'])>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-flame"/></svg> Promo
                </span>
            </a>
            <a href="{{ route('about') }}" @class(['text-brand-700' => request()->routeIs('about'), 'hover:text-brand-700' => !request()->routeIs('about'), 'transition'])>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-info"/></svg> Tentang
                </span>
            </a>
            <span class="ml-auto inline-flex items-center gap-1.5 text-xs text-ink-400">
                <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-truck"/></svg>
                Gratis ongkir belanja min. Rp300.000
            </span>
        </div>
    </nav>
</header>

<div data-mobile-menu class="fixed inset-0 z-50 hidden lg:hidden">
    <div data-mobile-backdrop class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"></div>
    <aside class="absolute left-0 top-0 h-full w-72 max-w-[80%] overflow-y-auto bg-white p-5 shadow-lift">
        <div class="mb-6 flex items-center justify-between">
            <span class="text-lg font-extrabold">Vinzy<span class="text-brand-600">Play</span></span>
            <button type="button" data-mobile-close class="flex h-9 w-9 items-center justify-center rounded-xl text-ink-500 hover:bg-ink-50" aria-label="Tutup menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-close"/></svg>
            </button>
        </div>
        <nav class="space-y-1 text-sm font-semibold text-ink-700">
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                <svg class="h-5 w-5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-store"/></svg> Beranda
            </a>
            <a href="{{ route('categories.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                <svg class="h-5 w-5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-grid"/></svg> Kategori
            </a>
            <a href="{{ route('promo') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                <svg class="h-5 w-5 text-danger-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-flame"/></svg> Promo
            </a>
            <a href="{{ route('about') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                <svg class="h-5 w-5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-info"/></svg> Tentang
            </a>
            @auth
                <div class="my-2 h-px bg-ink-100"></div>
                <a href="{{ route('account') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                    <svg class="h-5 w-5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-user"/></svg> Akun saya
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                    <svg class="h-5 w-5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-receipt"/></svg> Pesanan
                </a>
                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">
                    <svg class="h-5 w-5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-heart"/></svg> Wishlist
                </a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-brand-700 hover:bg-brand-50">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-shield"/></svg> Panel admin
                    </a>
                @endif
            @else
                <div class="my-2 h-px bg-ink-100"></div>
                <a href="{{ route('login') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-ink-50">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-3 py-3 text-center text-white">Daftar</a>
            @endauth
        </nav>
    </aside>
</div>

<main class="min-h-[60vh] pb-16">
    @yield('content')
</main>

{{-- Container Notifikasi Toast (Pop-up + Suara) --}}
<div id="toast-container" class="pointer-events-none fixed right-3 top-3 z-[9999] flex w-[calc(100vw-1.5rem)] max-w-sm flex-col gap-2.5 sm:right-5 sm:top-5"></div>

{{-- Trigger data pesan dari session (dibaca oleh JS) --}}
<div id="flash-data" class="hidden"
     data-success="{{ session('success') ?? session('status') }}"
     data-error="{{ session('error') }}"></div>

<footer class="border-t border-ink-100 bg-white pb-16 md:pb-0">
    {{-- Banner Jaminan Belanja (Tokopedia / Shopee Style) --}}
    <div class="border-b border-ink-100 bg-brand-50/50 py-6">
        <div class="container-page grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-sm">
                    <svg class="h-5.5 w-5.5"><use href="#i-shield"/></svg>
                </span>
                <div>
                    <p class="text-xs font-bold text-ink-900 sm:text-sm">100% Original</p>
                    <p class="text-[11px] text-ink-500">Semua produk resmi &amp; asli</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                    <svg class="h-5.5 w-5.5"><use href="#i-truck"/></svg>
                </span>
                <div>
                    <p class="text-xs font-bold text-ink-900 sm:text-sm">Bebas Ongkir</p>
                    <p class="text-[11px] text-ink-500">Belanja min. Rp300rb</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-sm">
                    <svg class="h-5.5 w-5.5"><use href="#i-refresh"/></svg>
                </span>
                <div>
                    <p class="text-xs font-bold text-ink-900 sm:text-sm">Retur 7 Hari</p>
                    <p class="text-[11px] text-ink-500">Komplain mudah &amp; cepat</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-sm">
                    <svg class="h-5.5 w-5.5"><use href="#i-headset"/></svg>
                </span>
                <div>
                    <p class="text-xs font-bold text-ink-900 sm:text-sm">CS Siaga 24/7</p>
                    <p class="text-[11px] text-ink-500">Respon ramah via AI &amp; tim</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-page grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-black text-white">N</span>
                <span class="text-lg font-extrabold">Vinzy<span class="text-brand-600">Play</span></span>
            </a>
            <p class="mt-4 text-sm leading-relaxed text-ink-500">
                Marketplace spesialis perlengkapan gaming, diecast koleksi, dan hobi terlengkap dengan pengiriman ke seluruh Indonesia.
            </p>
            <ul class="mt-5 space-y-2 text-sm text-ink-500">
                <li class="flex items-center gap-2"><svg class="h-4 w-4 text-brand-600 shrink-0"><use href="#i-map-pin"/></svg><span>Yogyakarta, DI Yogyakarta</span></li>
                <li class="flex items-center gap-2"><svg class="h-4 w-4 text-brand-600 shrink-0"><use href="#i-headset"/></svg><a href="tel:+6281234567890" class="hover:text-brand-700">+62 812-3456-7890</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-ink-900">Kategori Populer</h3>
            <nav class="mt-4 space-y-2 text-sm text-ink-500">
                @foreach (($footerCategories ?? collect()) as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}" class="block transition hover:text-brand-700">{{ $category->name }}</a>
                @endforeach
                <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700">Lihat Semua &rarr;</a>
            </nav>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-ink-900">Bantuan &amp; Panduan</h3>
            <nav class="mt-4 space-y-2 text-sm text-ink-500">
                <a href="{{ route('about') }}" class="block transition hover:text-brand-700">Tentang VinzyPlay</a>
                <a href="{{ route('promo') }}" class="block transition hover:text-brand-700">Katalog Promo &amp; Diskon</a>
                <a href="{{ route('about') }}" class="block transition hover:text-brand-700">Syarat &amp; Ketentuan</a>
                <a href="{{ route('about') }}" class="block transition hover:text-brand-700">Kebijakan Privasi</a>
                <a href="{{ route('about') }}" class="block transition hover:text-brand-700">Panduan Pembayaran &amp; Retur</a>
            </nav>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-ink-900">Metode Pembayaran</h3>
            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-bold text-ink-700">BCA</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-bold text-ink-700">Mandiri</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-bold text-ink-700">BRI</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-bold text-ink-700">BNI</span>
                <span class="rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700">QRIS</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-bold text-ink-700">GoPay</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-bold text-ink-700">ShopeePay</span>
                <span class="rounded-lg border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700">COD</span>
            </div>

            <h3 class="mt-5 text-sm font-bold uppercase tracking-wider text-ink-900">Jasa Pengiriman</h3>
            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-semibold text-ink-700">JNE Express</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-semibold text-ink-700">SiCepat</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-semibold text-ink-700">J&amp;T</span>
                <span class="rounded-lg border border-ink-200 bg-ink-50 px-2 py-1 text-[11px] font-semibold text-ink-700">Anteraja</span>
            </div>
        </div>
    </div>

    <div class="border-t border-ink-100 bg-ink-50/50">
        <div class="container-page flex flex-col gap-2 py-5 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
            <span class="font-medium tracking-wide uppercase">&copy; {{ now()->year }} VINZYPLAY &bull; MARKETPLACE GAMING, DIECAST &amp; HOBI</span>
            <span class="flex items-center gap-3">
                <span>Keamanan SSL 256-Bit</span>
                <span>&bull;</span>
                <span>Terverifikasi Resmi</span>
            </span>
        </div>
    </div>
</footer>

{{-- Bottom Mobile Navigation Bar (Tokopedia & Shopee Style) --}}
<nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-ink-200 bg-white/95 px-2 py-2 text-center text-[10px] font-semibold text-ink-600 shadow-lift backdrop-blur md:hidden">
    <a href="{{ route('home') }}" @class(['flex flex-col items-center gap-1 transition', 'text-brand-600 font-bold' => request()->routeIs('home')])>
        <svg class="h-5 w-5"><use href="#i-store"/></svg>
        <span>Beranda</span>
    </a>
    <a href="{{ route('categories.index') }}" @class(['flex flex-col items-center gap-1 transition', 'text-brand-600 font-bold' => request()->routeIs('categories.index')])>
        <svg class="h-5 w-5"><use href="#i-grid"/></svg>
        <span>Kategori</span>
    </a>
    <a href="{{ route('promo') }}" @class(['relative flex flex-col items-center gap-1 transition', 'text-danger-600 font-bold' => request()->routeIs('promo'), 'text-ink-600' => !request()->routeIs('promo')])>
        <svg class="h-5 w-5 text-danger-500"><use href="#i-flame"/></svg>
        <span class="text-danger-600">Promo</span>
        <span class="absolute -top-0.5 right-1.5 h-2 w-2 rounded-full bg-danger-500"></span>
    </a>
    <a href="{{ route('cart.index') }}" @class(['relative flex flex-col items-center gap-1 transition', 'text-brand-600 font-bold' => request()->routeIs('cart.index')])>
        <svg class="h-5 w-5"><use href="#i-cart"/></svg>
        <span>Keranjang</span>
        @auth
            @if (($headerCartCount ?? 0) > 0)
                <span class="absolute -top-1 right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-500 px-1 text-[9px] font-bold text-white">{{ $headerCartCount }}</span>
            @endif
        @endauth
    </a>
    <a href="{{ auth()->check() ? route('account') : route('login') }}" @class(['flex flex-col items-center gap-1 transition', 'text-brand-600 font-bold' => request()->routeIs('account', 'login')])>
        <svg class="h-5 w-5"><use href="#i-user"/></svg>
        <span>{{ auth()->check() ? 'Akun' : 'Masuk' }}</span>
    </a>
</nav>

@include('partials.chatbot')

</body>
</html>
