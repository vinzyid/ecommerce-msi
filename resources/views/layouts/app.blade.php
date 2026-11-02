<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="{{ config('app.name') }}, toko online perlengkapan gaming, diecast, dan hobi koleksi.">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name').' — Perlengkapan Gaming, Diecast & Hobi')</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.icons')
</head>
<body class="min-h-screen bg-white text-ink-900 antialiased @yield('body-class')">
@php
    $navItems = [
        ['home', 'Beranda', ['home']],
        ['categories.index', 'Kategori', ['categories.index']],
        ['promo', 'Promo', ['promo']],
        ['about', 'Tentang', ['about']],
    ];
    $headerCartCount = auth()->check() ? (int) auth()->user()->cartItems()->sum('quantity') : 0;
    $headerWishlistCount = auth()->check() ? auth()->user()->wishlists()->count() : 0;
@endphp

{{-- Pengumuman satu baris --}}
<div class="bg-ink-50 text-center text-xs text-ink-600">
    <p class="container-page truncate py-2">
        Gratis ongkir untuk belanja min. Rp300.000 &middot; Produk original bergaransi resmi
        <a href="{{ route('promo') }}" class="ml-1 font-medium text-brand-600 hover:underline">Lihat promo &rsaquo;</a>
    </p>
</div>

<header class="sticky top-0 z-40 border-b border-ink-100 bg-white/80 backdrop-blur-xl supports-[backdrop-filter]:bg-white/70">
    <div class="container-page">
        <div class="flex h-16 items-center gap-3 lg:gap-8">
            <button type="button" data-mobile-toggle
                    class="-ml-2 grid h-10 w-10 place-items-center rounded-full text-ink-700 hover:bg-ink-50 lg:hidden"
                    aria-label="Buka menu" aria-expanded="false">
                <svg class="h-5.5 w-5.5" aria-hidden="true"><use href="#i-menu"/></svg>
            </button>

            <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ config('app.name') }}, halaman utama">
                <x-logo />
            </a>

            <nav class="hidden items-center gap-7 text-sm lg:flex" aria-label="Navigasi utama">
                @foreach ($navItems as [$route, $label, $active])
                    <a href="{{ route($route) }}" @class([
                        'transition-colors',
                        'font-medium text-ink-900' => request()->routeIs(...$active),
                        'text-ink-500 hover:text-ink-900' => ! request()->routeIs(...$active),
                    ])>{{ $label }}</a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('home') }}" role="search" data-search-form data-suggest-url="{{ route('search.suggest') }}" class="relative ml-auto hidden w-full max-w-xs md:block lg:max-w-sm">
                <label class="sr-only" for="site-search">Cari produk</label>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="site-search" name="q" type="search" autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list" value="{{ request('q') }}"
                       placeholder="Cari produk"
                       class="w-full rounded-full border border-transparent bg-ink-50 py-2 pl-10 pr-4 text-sm outline-none transition placeholder:text-ink-400 focus:border-ink-200 focus:bg-white focus:ring-4 focus:ring-ink-100">
            </form>

            <div class="ml-auto flex items-center gap-1 md:ml-0">
                @auth
                    <a href="{{ route('wishlist.index') }}"
                       class="relative hidden h-10 w-10 place-items-center rounded-full text-ink-700 hover:bg-ink-50 sm:grid" aria-label="Wishlist">
                        <svg class="h-5 w-5" aria-hidden="true"><use href="#i-heart"/></svg>
                        @if ($headerWishlistCount > 0)
                            <span class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full bg-ink-900 px-1 text-[10px] font-semibold tabular-nums text-white">{{ $headerWishlistCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('cart.index') }}"
                       class="relative grid h-10 w-10 place-items-center rounded-full text-ink-700 hover:bg-ink-50" aria-label="Keranjang">
                        <svg class="h-5 w-5" aria-hidden="true"><use href="#i-cart"/></svg>
                        @if ($headerCartCount > 0)
                            <span class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-semibold tabular-nums text-white">{{ $headerCartCount }}</span>
                        @endif
                    </a>

                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger aria-haspopup="true" aria-expanded="false"
                                class="ml-1 grid h-9 w-9 place-items-center rounded-full bg-ink-900 text-xs font-semibold text-white hover:bg-ink-700"
                                aria-label="Menu akun {{ auth()->user()->username }}">
                            {{ auth()->user()->initials() }}
                        </button>
                        <div data-dropdown-menu
                             class="invisible absolute right-0 top-full z-50 mt-2 w-60 origin-top-right scale-95 rounded-2xl border border-ink-100 bg-white p-1.5 opacity-0 shadow-lift transition-[opacity,scale,visibility] duration-150 ease-(--ease-out)">
                            <div class="px-3 pb-2 pt-2.5">
                                <p class="truncate text-sm font-medium text-ink-900">{{ auth()->user()->username }}</p>
                                <p class="truncate text-xs text-ink-500">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="my-1 h-px bg-ink-100"></div>
                            @foreach ([
                                ['account', 'i-user', 'Akun saya'],
                                ['orders.index', 'i-receipt', 'Pesanan'],
                                ['wishlist.index', 'i-heart', 'Wishlist'],
                            ] as [$route, $icon, $label])
                                <a href="{{ route($route) }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">
                                    <svg class="h-4.5 w-4.5 text-ink-400" aria-hidden="true"><use href="#{{ $icon }}"/></svg> {{ $label }}
                                </a>
                            @endforeach
                            @if (auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-ink-700 hover:bg-ink-50">
                                    <svg class="h-4.5 w-4.5 text-ink-400" aria-hidden="true"><use href="#i-shield"/></svg> Panel admin
                                </a>
                            @endif
                            <div class="my-1 h-px bg-ink-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-danger-600 hover:bg-danger-500/10">
                                    <svg class="h-4.5 w-4.5" aria-hidden="true"><use href="#i-logout"/></svg> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-full px-4 py-2 text-sm text-ink-700 hover:bg-ink-50 sm:inline-block">Masuk</a>
                    <a href="{{ route('register') }}"
                       class="press rounded-full bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-700">Daftar</a>
                @endauth
            </div>
        </div>

        <form method="GET" action="{{ route('home') }}" role="search" data-search-form data-suggest-url="{{ route('search.suggest') }}" class="pb-3 md:hidden">
            <label class="sr-only" for="site-search-mobile">Cari produk</label>
            <div class="relative" data-search-anchor>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="site-search-mobile" name="q" type="search" autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list" value="{{ request('q') }}"
                       placeholder="Cari produk gaming, diecast, hobi"
                       class="w-full rounded-full border border-transparent bg-ink-50 py-2.5 pl-10 pr-4 text-base outline-none placeholder:text-ink-400 focus:border-ink-200 focus:bg-white focus:ring-4 focus:ring-ink-100">
            </div>
        </form>
    </div>
</header>

<div data-mobile-menu class="fixed inset-0 z-50 hidden lg:hidden">
    <div data-mobile-backdrop class="absolute inset-0 bg-ink-950/40 backdrop-blur-sm"></div>
    <aside data-mobile-panel class="absolute left-0 top-0 h-full w-80 max-w-[85%] overflow-y-auto overscroll-contain bg-white p-6 pt-[max(1.5rem,env(safe-area-inset-top))] shadow-lift">
        <div class="mb-8 flex items-center justify-between">
            <x-logo />
            <button type="button" data-mobile-close class="-mr-2 grid h-10 w-10 place-items-center rounded-full text-ink-500 hover:bg-ink-50" aria-label="Tutup menu">
                <svg class="h-5 w-5" aria-hidden="true"><use href="#i-close"/></svg>
            </button>
        </div>
        <nav class="space-y-1 text-[17px]">
            @foreach ($navItems as [$route, $label, $active])
                <a href="{{ route($route) }}" @class([
                    'block rounded-xl px-3 py-2.5',
                    'font-medium text-ink-900 bg-ink-50' => request()->routeIs(...$active),
                    'text-ink-700 hover:bg-ink-50' => ! request()->routeIs(...$active),
                ])>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="my-6 h-px bg-ink-100"></div>
        <nav class="space-y-1 text-sm text-ink-700">
            @auth
                <a href="{{ route('account') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-ink-50"><svg class="h-5 w-5 text-ink-400" aria-hidden="true"><use href="#i-user"/></svg> Akun saya</a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-ink-50"><svg class="h-5 w-5 text-ink-400" aria-hidden="true"><use href="#i-receipt"/></svg> Pesanan</a>
                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-ink-50"><svg class="h-5 w-5 text-ink-400" aria-hidden="true"><use href="#i-heart"/></svg> Wishlist</a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-ink-50"><svg class="h-5 w-5 text-ink-400" aria-hidden="true"><use href="#i-shield"/></svg> Panel admin</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="block rounded-full border border-ink-200 px-4 py-2.5 text-center font-medium text-ink-900">Masuk</a>
                <a href="{{ route('register') }}" class="mt-2 block rounded-full bg-ink-900 px-4 py-2.5 text-center font-medium text-white">Daftar</a>
            @endauth
        </nav>
    </aside>
</div>

<main class="min-h-[60vh] pb-24 md:pb-20">
    @yield('content')
</main>

{{-- Container Notifikasi Toast (Pop-up + Suara) --}}
<div id="toast-container" class="pointer-events-none fixed right-3 top-3 z-[9999] flex w-[calc(100vw-1.5rem)] max-w-sm flex-col gap-2.5 sm:right-5 sm:top-5"></div>

{{-- Trigger data pesan dari session (dibaca oleh JS) --}}
<div id="flash-data" class="hidden"
     data-success="{{ session('success') ?? session('status') }}"
     data-error="{{ session('error') }}"></div>

<footer class="border-t border-ink-100 bg-ink-50 pb-20 text-sm md:pb-0">
    {{-- Jaminan belanja --}}
    <div class="container-page grid grid-cols-2 gap-6 border-b border-ink-200/70 py-10 lg:grid-cols-4">
        @foreach ([
            ['i-shield', '100% original', 'Garansi resmi distributor & brand.'],
            ['i-truck', 'Gratis ongkir', 'Untuk belanja min. Rp300.000.'],
            ['i-refresh', 'Retur 7 hari', 'Barang rusak atau salah bisa diajukan untuk diganti.'],
            ['i-headset', 'Asisten toko', 'Tanya harga, stok, dan cara belanja lewat chat.'],
        ] as [$icon, $title, $desc])
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-900" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
                <div>
                    <p class="font-medium text-ink-900">{{ $title }}</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-ink-500">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
        <div>
            <a href="{{ route('home') }}" aria-label="{{ config('app.name') }}, halaman utama"><x-logo /></a>
            <p class="mt-4 max-w-xs text-xs leading-relaxed text-ink-500">
                Perlengkapan gaming, komponen PC, dan diecast koleksi pilihan, dikirim ke seluruh Indonesia.
            </p>
            <ul class="mt-5 space-y-2 text-xs text-ink-500">
                <li class="flex items-center gap-2"><svg class="h-4 w-4 shrink-0 text-ink-400" aria-hidden="true"><use href="#i-map-pin"/></svg>Yogyakarta, DI Yogyakarta</li>
                <li class="flex items-center gap-2"><svg class="h-4 w-4 shrink-0 text-ink-400" aria-hidden="true"><use href="#i-headset"/></svg><a href="tel:+6281234567890" class="hover:text-ink-900">+62 812-3456-7890</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-xs font-semibold text-ink-900">Belanja</h3>
            <nav class="mt-4 space-y-2.5 text-xs text-ink-500">
                @foreach (($footerCategories ?? collect()) as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}" class="block hover:text-ink-900">{{ $category->name }}</a>
                @endforeach
                <a href="{{ route('categories.index') }}" class="block font-medium text-brand-600 hover:underline">Semua kategori</a>
            </nav>
        </div>

        <div>
            <h3 class="text-xs font-semibold text-ink-900">Bantuan</h3>
            <nav class="mt-4 space-y-2.5 text-xs text-ink-500">
                <a href="{{ route('about') }}" class="block hover:text-ink-900">Tentang {{ config('app.name') }}</a>
                <a href="{{ route('promo') }}" class="block hover:text-ink-900">Promo &amp; voucher</a>
                <a href="{{ route('pages.terms') }}" class="block hover:text-ink-900">Syarat &amp; ketentuan</a>
                <a href="{{ route('pages.privacy') }}" class="block hover:text-ink-900">Kebijakan privasi</a>
                <a href="{{ route('pages.returns') }}" class="block hover:text-ink-900">Pembayaran &amp; retur</a>
            </nav>
        </div>

        <div>
            <h3 class="text-xs font-semibold text-ink-900">Pembayaran</h3>
            <p class="mt-4 text-xs leading-relaxed text-ink-500">BCA, Mandiri, BRI, BNI, QRIS, COD</p>
            <h3 class="mt-6 text-xs font-semibold text-ink-900">Pengiriman</h3>
            <p class="mt-4 text-xs leading-relaxed text-ink-500">JNE Regular, JNE YES</p>
        </div>
    </div>

    <div class="border-t border-ink-200/70">
        <div class="container-page flex flex-col gap-2 py-5 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ now()->year }} {{ config('app.name') }}. Semua hak dilindungi.</span>
            <span>Indonesia &middot; Bahasa Indonesia</span>
        </div>
    </div>
</footer>

{{-- Navigasi bawah (ponsel) --}}
<nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-ink-100 bg-white/85 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 text-center text-[10px] backdrop-blur-xl md:hidden"
     aria-label="Navigasi bawah">
    @foreach ([
        [route('home'), 'i-store', 'Beranda', request()->routeIs('home')],
        [route('categories.index'), 'i-grid', 'Kategori', request()->routeIs('categories.index')],
        [route('promo'), 'i-tag', 'Promo', request()->routeIs('promo')],
        [route('cart.index'), 'i-cart', 'Keranjang', request()->routeIs('cart.index')],
        [auth()->check() ? route('account') : route('login'), 'i-user', auth()->check() ? 'Akun' : 'Masuk', request()->routeIs('account', 'login')],
    ] as [$href, $icon, $label, $isActive])
        <a href="{{ $href }}" @class([
            'relative flex min-w-14 flex-col items-center gap-1 py-0.5',
            'font-medium text-ink-900' => $isActive,
            'text-ink-400' => ! $isActive,
        ]) @if ($isActive) aria-current="page" @endif>
            <svg class="h-5.5 w-5.5" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
            <span>{{ $label }}</span>
            @if ($icon === 'i-cart' && $headerCartCount > 0)
                <span class="absolute -top-1 right-2.5 grid h-4 min-w-4 place-items-center rounded-full bg-brand-600 px-1 text-[9px] font-semibold tabular-nums text-white">{{ $headerCartCount }}</span>
            @endif
        </a>
    @endforeach
</nav>

@include('partials.chatbot')

</body>
</html>
