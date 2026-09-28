<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Etalase, toko online modern untuk kebutuhan rumah dan ruang kerja.">
    <title>@yield('title', 'Etalase — Modern Lifestyle & Work Essentials')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="@yield('body-class')">
    <svg class="icon-sprite" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
        <symbol id="i-store" viewBox="0 0 24 24"><path d="M3 9.5 4.6 4h14.8L21 9.5M3 9.5V19a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9.5M3 9.5h18M9 20v-5h6v5"/></symbol>
        <symbol id="i-receipt" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3zM9 8h6M9 12h6"/></symbol>
        <symbol id="i-cart" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h2.2l2.4 12.2a1 1 0 0 0 1 .8h9.8a1 1 0 0 0 1-.8L20.5 7H5"/></symbol>
        <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4.2 2.9 7.6 7 9 4.1-1.4 7-4.8 7-9V6l-7-3z"/></symbol>
        <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.5 3.1-5.6 7-5.6s7 2.1 7 5.6"/></symbol>
        <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="7" height="7" rx="1.4"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.4"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.4"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.4"/></symbol>
        <symbol id="i-box" viewBox="0 0 24 24"><path d="M3.5 7.5 12 3.5l8.5 4v9L12 20.5l-8.5-4v-9zM3.5 7.5 12 11.5l8.5-4M12 11.5v9"/></symbol>
        <symbol id="i-layers" viewBox="0 0 24 24"><path d="M12 3.5 3 8l9 4.5L21 8l-9-4.5zM3 12.5 12 17l9-4.5M3 16.5 12 21l9-4.5"/></symbol>
        <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M2.5 20c0-3.3 2.9-5.3 6.5-5.3s6.5 2 6.5 5.3M16.5 5.2a3.2 3.2 0 0 1 0 6M18 14.9c2.1.5 3.5 2 3.5 4.1"/></symbol>
    </svg>

    <div class="scene-orbs" aria-hidden="true">
        <i></i><i></i><i></i><i></i>
    </div>

    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="Etalase, halaman katalog">
            <span class="brand-mark" aria-hidden="true">E</span>
            <span class="brand-name">ETALASE</span>
        </a>

        <nav class="main-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home', 'products.show')])>
                <svg class="icon" aria-hidden="true"><use href="#i-store"/></svg><span>Belanja</span>
            </a>
            @auth
                <a href="{{ route('orders.index') }}" @class(['active' => request()->routeIs('orders.*')])>
                    <svg class="icon" aria-hidden="true"><use href="#i-receipt"/></svg><span>Pesanan</span>
                </a>
                <a href="{{ route('cart.index') }}" @class(['active' => request()->routeIs('cart.*', 'checkout.*')])>
                    <svg class="icon" aria-hidden="true"><use href="#i-cart"/></svg><span>Cart</span>
                    <span class="nav-count">{{ auth()->user()->cartItems()->sum('quantity') }}</span>
                </a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.*')])>
                        <svg class="icon" aria-hidden="true"><use href="#i-shield"/></svg><span>Admin</span>
                    </a>
                @endif
                <a class="nav-account" href="{{ route('account') }}" @class(['active' => request()->routeIs('account')])>
                    <span class="avatar avatar-sm" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                    <span>Akun</span>
                </a>
            @else
                <a href="{{ route('login') }}" @class(['active' => request()->routeIs('login')])>
                    <svg class="icon" aria-hidden="true"><use href="#i-user"/></svg><span>Masuk</span>
                </a>
                <a class="nav-register" href="{{ route('register') }}">
                    <svg class="icon" aria-hidden="true"><use href="#i-user"/></svg><span>Daftar</span>
                </a>
            @endauth
        </nav>
    </header>

    @if (session('success'))
        <div class="flash flash-success" role="status">{{ session('success') }}</div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-columns">
            <div class="footer-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="Etalase, halaman katalog">
                    <span class="brand-mark" aria-hidden="true">E</span>
                    <span class="brand-name">ETALASE</span>
                </a>
                <p>Kebutuhan rumah dan meja kerja, dikirim dari gudang kami ke alamat Anda.</p>
                <ul class="footer-contact">
                    <li><b>Kota</b><span>Bandung, Jawa Barat</span></li>
                    <li><b>Telepon</b><a href="tel:+6281234567890">+62 812-3456-7890</a></li>
                    <li><b>Email</b><a href="mailto:halo@etalase.test">halo@etalase.test</a></li>
                </ul>
            </div>

            <nav class="footer-links" aria-label="Kategori produk">
                <h3>Kategori</h3>
                @foreach ($footerCategories as $category)
                    <a href="{{ route('home', ['category' => $category->slug]) }}">{{ $category->name }}</a>
                @endforeach
            </nav>

            <div class="footer-links">
                <h3>Pembayaran</h3>
                <span class="footer-badge">COD</span>
                <span class="footer-badge">Transfer Bank</span>
                <p class="footer-note">Pilih metode pembayaran saat checkout.</p>
            </div>

            <div class="footer-newsletter">
                <h3>Newsletter</h3>
                <p>Dapatkan info produk baru dan promo.</p>
                <form class="newsletter-form" data-newsletter>
                    <label for="newsletter-email">Alamat email</label>
                    <div>
                        <input id="newsletter-email" type="email" placeholder="nama@email.com" required>
                        <button type="submit">Kirim</button>
                    </div>
                    <small class="newsletter-message" data-newsletter-message hidden>Fitur segera hadir. Terima kasih sudah mendaftar!</small>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <span>ETALASE &bull; MODERN LIFESTYLE COMMERCE</span>
            <span>Dikembangkan oleh Rafi Pandya P &copy; {{ now()->year }}</span>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
