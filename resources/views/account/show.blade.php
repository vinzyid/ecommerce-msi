@extends('layouts.app')

@section('title', 'Akun — Etalase')

@section('content')
<div class="account-shell">
    <header class="account-heading">
        <span class="section-code">AKUN / DATA PELANGGAN</span>
        <h1>Halo, {{ $user->username }}.</h1>
        <p>Kelola data diri, pantau belanja, dan telusuri status pesanan dari satu halaman.</p>
    </header>

    <section class="account-stats" aria-label="Ringkasan aktivitas akun">
        <a class="stat-card" href="{{ route('orders.index') }}">
            <span>Total pesanan</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>semua transaksi</small>
        </a>
        <a class="stat-card" href="{{ route('orders.index') }}">
            <span>Sedang diproses</span>
            <strong>{{ $stats['active'] }}</strong>
            <small>menunggu sampai dikirim</small>
        </a>
        <a class="stat-card" href="{{ route('orders.index') }}">
            <span>Total belanja</span>
            <strong>Rp{{ number_format($stats['spent'], 0, ',', '.') }}</strong>
            <small>pesanan diselesaikan</small>
        </a>
    </section>

    <div class="account-grid">
        <section class="recent-orders" aria-labelledby="recent-heading">
            <header class="section-heading">
                <h2 id="recent-heading">Pesanan terakhir</h2>
                <a href="{{ route('orders.index') }}">Lihat semua</a>
            </header>

            @forelse ($orders as $order)
                <a class="recent-order" href="{{ route('orders.show', $order) }}">
                    <span class="recent-identity">
                        <small>{{ $order->ordered_at->format('d M Y') }}</small>
                        <strong>{{ $order->order_number }}</strong>
                        <em>{{ $order->items_count }} barang</em>
                    </span>
                    <strong class="recent-total">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                    <b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b>
                </a>
            @empty
                <div class="empty-state bordered recent-empty">
                    <h2>Belum ada pesanan</h2>
                    <p>Pesanan yang selesai di-checkout akan tampil di sini.</p>
                    <a class="text-link" href="{{ route('home') }}">Buka katalog</a>
                </div>
            @endforelse
        </section>

        <aside class="account-side">
            <section class="account-data">
                <div class="data-heading">
                    <h2>Data akun</h2>
                    <span class="login-status"><i aria-hidden="true"></i> Aktif</span>
                </div>
                <dl>
                    <div><dt>Username</dt><dd>{{ $user->username }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                    <div><dt>Jenis akun</dt><dd>{{ $user->is_admin ? 'Admin' : 'Pelanggan' }}</dd></div>
                    <div><dt>Terdaftar</dt><dd>{{ $user->created_at->format('d M Y') }}</dd></div>
                </dl>
            </section>

            <section class="account-actions" aria-label="Aksi akun">
                <a class="secondary-button button-link" href="{{ route('cart.index') }}">
                    Keranjang belanja <b class="nav-count">{{ $cartCount }}</b>
                </a>
                @if ($user->is_admin)
                    <a class="secondary-button button-link" href="{{ route('admin.dashboard') }}">Buka panel admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="secondary-button" type="submit">Keluar dari akun</button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
