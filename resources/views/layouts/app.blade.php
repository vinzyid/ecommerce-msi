<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Etalase, toko online untuk kebutuhan rumah dan kerja.">
    <title>@yield('title', 'Etalase')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formal.css') }}">
</head>
<body class="@yield('body-class')">
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
        <span>ETALASE / TOKO ONLINE</span>
        <span>Dikembangkan oleh Rafi Pandya P &copy; {{ now()->year }}</span>
    </footer>
</body>
</html>
