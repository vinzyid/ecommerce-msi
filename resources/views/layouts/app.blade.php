<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name') }}, toko online perlengkapan gaming, diecast, dan hobi koleksi.">
    <title>@yield('title', config('app.name').' — Perlengkapan Gaming, Diecast & Hobi')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.icons')
</head>
<body class="min-h-screen bg-[#f4f6fb] text-ink-900 antialiased @yield('body-class')">

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
                <span class="hidden text-lg font-extrabold tracking-tight text-ink-900 sm:block">NADI<span class="text-brand-600">PLAY</span></span>
            </a>

            <form method="GET" action="{{ route('home') }}" role="search" class="relative hidden flex-1 md:block">
                <label class="sr-only" for="site-search">Cari produk</label>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="site-search" name="q" type="search"
                       value="{{ request()->routeIs('home') ? request('q') : '' }}"
                       placeholder="Cari keyboard, diecast, headset..."
                       class="w-full rounded-xl border border-ink-200 bg-ink-50 py-2.5 pl-10 pr-24 text-sm outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20">
                <button type="submit"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Cari
                </button>
            </form>

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
                <input id="site-search-mobile" name="q" type="search" value="{{ request()->routeIs('home') ? request('q') : '' }}"
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
            <span class="text-lg font-extrabold">NADI<span class="text-brand-600">PLAY</span></span>
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

@if (session('success') || session('status'))
    <div class="border-b border-success-500/20 bg-success-500/10">
        <div class="container-page flex items-center gap-2.5 py-3 text-sm font-semibold text-success-600">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-check-circle"/></svg>
            {{ session('success') ?? session('status') }}
        </div>
    </div>
@endif

@if (session('error'))
    <div class="border-b border-danger-500/20 bg-danger-500/10">
        <div class="container-page flex items-center gap-2.5 py-3 text-sm font-semibold text-danger-600">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-info"/></svg>
            {{ session('error') }}
        </div>
    </div>
@endif

<main class="min-h-[60vh] pb-16">
    @yield('content')
</main>

<footer class="border-t border-ink-100 bg-white">
    <div class="container-page grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-black text-white">N</span>
                <span class="text-lg font-extrabold">NADI<span class="text-brand-600">PLAY</span></span>
            </a>
            <p class="mt-4 text-sm leading-relaxed text-ink-500">
                Toko online perlengkapan gaming, diecast, dan hobi koleksi. Pilihan gear untuk main, kerja, dan koleksi.
            </p>
            <ul class="mt-5 space-y-2.5 text-sm text-ink-500">
                <li class="flex gap-2"><b class="min-w-16 text-ink-700">Kota</b><span>Yogyakarta, DI Yogyakarta</span></li>
                <li class="flex gap-2"><b class="min-w-16 text-ink-700">Telepon</b><a href="tel:+6281234567890" class="hover:text-brand-700">+62 812-3456-7890</a></li>
                <li class="flex gap-2"><b class="min-w-16 text-ink-700">Email</b><a href="mailto:halo@nadiplay.test" class="hover:text-brand-700">halo@nadiplay.test</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-900">Kategori</h3>
            <nav class="mt-4 space-y-2.5 text-sm text-ink-500">
                @foreach (($footerCategories ?? collect()) as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}" class="block hover:text-brand-700">{{ $category->name }}</a>
                @endforeach
            </nav>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-900">Bantuan</h3>
            <nav class="mt-4 space-y-2.5 text-sm text-ink-500">
                <a href="{{ route('about') }}" class="block hover:text-brand-700">Tentang kami</a>
                <a href="{{ route('promo') }}" class="block hover:text-brand-700">Promo berjalan</a>
                <a href="{{ route('categories.index') }}" class="block hover:text-brand-700">Semua kategori</a>
                <a href="{{ route('login') }}" class="block hover:text-brand-700">Masuk akun</a>
            </nav>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-900">Pembayaran</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600">COD</span>
                <span class="rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600">Transfer Bank</span>
            </div>
            <p class="mt-4 text-xs text-ink-400">Pilih metode pembayaran saat checkout. Garansi resmi &amp; retur 7 hari.</p>
        </div>
    </div>

    <div class="border-t border-ink-100">
        <div class="container-page flex flex-col gap-2 py-5 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
            <span class="uppercase tracking-wide">NADI PLAY &bull; GAMING, DIECAST &amp; HOBI</span>
            <span>Dikembangkan oleh Rafi Pandya P &copy; {{ now()->year }}</span>
        </div>
    </div>
</footer>

</body>
</html>
