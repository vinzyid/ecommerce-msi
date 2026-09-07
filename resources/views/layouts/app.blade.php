<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Etalase, toko online modern untuk kebutuhan rumah dan ruang kerja.">
    <title>@yield('title', 'Etalase — Modern Lifestyle & Work Essentials')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formal.css') }}">
</head>
<body class="@yield('body-class')">
    <div class="scene-orbs" aria-hidden="true">
        <i></i><i></i><i></i><i></i>
    </div>

    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="Etalase, halaman katalog">
            <span class="brand-mark" aria-hidden="true">E</span>
            <span class="brand-name">ETALASE</span>
        </a>

        <nav class="main-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home', 'products.show')])>Belanja</a>
            @auth
                <a href="{{ route('orders.index') }}" @class(['active' => request()->routeIs('orders.*')])>Pesanan</a>
                <a href="{{ route('cart.index') }}" @class(['active' => request()->routeIs('cart.*', 'checkout.*')])>
                    Cart <span class="nav-count">{{ auth()->user()->cartItems()->sum('quantity') }}</span>
                </a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.*')])>Admin</a>
                @endif
                <a href="{{ route('account') }}" @class(['active' => request()->routeIs('account')])>Akun</a>
            @else
                <a href="{{ route('login') }}" @class(['active' => request()->routeIs('login')])>Masuk</a>
                <a class="nav-register" href="{{ route('register') }}">Daftar</a>
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
