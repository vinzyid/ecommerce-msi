<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name') }}, toko online perlengkapan rumah, meja kerja, dan kebutuhan harian.">
    <title>@yield('title', config('app.name').' — Perlengkapan Rumah dan Meja Kerja')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    @include('partials.icons')
</head>
<body class="@yield('body-class')">
    <div class="scene-orbs" aria-hidden="true"><i></i><i></i><i></i><i></i></div>

    <header class="site-header">
        <div class="header-top">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ config('app.name') }}, halaman utama">
                <span class="brand-mark" aria-hidden="true">{{ strtoupper(substr(config('app.name'), 0, 1)) }}</span>
                <span class="brand-name">{{ strtoupper(config('app.name')) }}</span>
            </a>

            <form class="header-search" method="GET" action="{{ route('home') }}" role="search">
                <label class="sr-only" for="site-search">Cari produk</label>
                <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="site-search" name="q" type="search" value="{{ request()->routeIs('home') ? request('q') : '' }}" placeholder="Cari produk, kategori, atau merek...">
                <button type="submit" aria-label="Cari"><svg class="icon" aria-hidden="true"><use href="#i-search"/></svg></button>
            </form>

            <div class="header-actions">
                @auth
                    <a class="icon-button" href="{{ route('wishlist.index') }}" aria-label="Wishlist">
                        <svg class="icon" aria-hidden="true"><use href="#i-heart"/></svg>
                        @if (($headerWishlistCount = auth()->user()->wishlists()->count()) > 0)
                            <span class="nav-count">{{ $headerWishlistCount }}</span>
                        @endif
                    </a>
                    <a class="icon-button" href="{{ route('cart.index') }}" aria-label="Cart">
                        <svg class="icon" aria-hidden="true"><use href="#i-cart"/></svg>
                        @if (($headerCartCount = auth()->user()->cartItems()->sum('quantity')) > 0)
                            <span class="nav-count">{{ $headerCartCount }}</span>
                        @endif
                    </a>
                    <div class="account-menu">
                        <button class="account-trigger" type="button" aria-haspopup="true" aria-expanded="false">
                            <span class="avatar avatar-sm" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                            <span>{{ auth()->user()->username }}</span>
                            <svg class="icon" aria-hidden="true"><use href="#i-chevron"/></svg>
                        </button>
                        <div class="account-dropdown">
                            <a href="{{ route('account') }}"><svg class="icon" aria-hidden="true"><use href="#i-user"/></svg> Akun saya</a>
                            <a href="{{ route('orders.index') }}"><svg class="icon" aria-hidden="true"><use href="#i-receipt"/></svg> Pesanan</a>
                            <a href="{{ route('wishlist.index') }}"><svg class="icon" aria-hidden="true"><use href="#i-heart"/></svg> Wishlist</a>
                            @if (auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}"><svg class="icon" aria-hidden="true"><use href="#i-shield"/></svg> Panel admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"><svg class="icon" aria-hidden="true"><use href="#i-logout"/></svg> Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="text-button" href="{{ route('login') }}">Masuk</a>
                    <a class="primary-button button-link fit" href="{{ route('register') }}">Daftar</a>
                @endauth
            </div>

            <button class="mobile-menu-toggle" type="button" aria-label="Buka menu" aria-expanded="false">
                <svg class="icon" aria-hidden="true"><use href="#i-menu"/></svg>
            </button>
        </div>

        <nav class="main-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>
                <svg class="icon" aria-hidden="true"><use href="#i-store"/></svg><span>Beranda</span>
            </a>
            <a href="{{ route('categories.index') }}" @class(['active' => request()->routeIs('categories.index', 'products.show')])>
                <svg class="icon" aria-hidden="true"><use href="#i-grid"/></svg><span>Kategori</span>
            </a>
            <a href="{{ route('promo') }}" @class(['active' => request()->routeIs('promo')])>
                <svg class="icon" aria-hidden="true"><use href="#i-tag"/></svg><span>Promo</span>
            </a>
            <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>
                <svg class="icon" aria-hidden="true"><use href="#i-info"/></svg><span>Tentang</span>
            </a>
        </nav>
    </header>

    @if (session('success'))
        <div class="flash flash-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('status'))
        <div class="flash flash-success" role="status">{{ session('status') }}</div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-columns">
            <div class="footer-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="{{ config('app.name') }}, halaman utama">
                    <span class="brand-mark" aria-hidden="true">{{ strtoupper(substr(config('app.name'), 0, 1)) }}</span>
                    <span class="brand-name">{{ strtoupper(config('app.name')) }}</span>
                </a>
                <p>Perlengkapan rumah, meja kerja, dan kebutuhan harian yang dipilih untuk dipakai setiap hari.</p>
                <ul class="footer-contact">
                    <li><b>Kota</b><span>Yogyakarta, DI Yogyakarta</span></li>
                    <li><b>Telepon</b><a href="tel:+6281234567890">+62 812-3456-7890</a></li>
                    <li><b>Email</b><a href="mailto:halo@nadimarket.test">halo@nadimarket.test</a></li>
                </ul>
            </div>

            <nav class="footer-links" aria-label="Kategori produk">
                <h3>Kategori</h3>
                @foreach (($footerCategories ?? collect()) as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}">{{ $category->name }}</a>
                @endforeach
            </nav>

            <nav class="footer-links" aria-label="Bantuan">
                <h3>Bantuan</h3>
                <a href="{{ route('about') }}">Tentang kami</a>
                <a href="{{ route('promo') }}">Promo berjalan</a>
                <a href="{{ route('categories.index') }}">Semua kategori</a>
                <a href="{{ route('login') }}">Masuk akun</a>
            </nav>

            <div class="footer-links">
                <h3>Pembayaran</h3>
                <span class="footer-badge">COD</span>
                <span class="footer-badge">Transfer Bank</span>
                <p class="footer-note">Pilih metode pembayaran saat checkout.</p>
            </div>
        </div>

        <div class="footer-bottom">
            <span>{{ strtoupper(config('app.name')) }} &bull; PERLENGKAPAN RUMAH DAN KERJA</span>
            <span>Dikembangkan oleh Rafi Pandya P &copy; {{ now()->year }}</span>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
