@extends('layouts.app')

@section('title', 'Pengguna '.$user->username.' — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <div class="breadcrumb"><a href="{{ route('admin.users.index') }}">Pengguna</a><span>/</span><b>{{ $user->username }}</b></div>

    <header class="order-detail-heading">
        <div>
            <span class="section-code">ADMIN / PENGGUNA</span>
            <h1>{{ $user->username }}</h1>
        </div>
        <b class="status {{ $user->is_admin ? 'status-shipped' : 'status-completed' }}">{{ $user->is_admin ? 'Admin' : 'Pelanggan' }}</b>
    </header>

    @error('is_admin')<div class="alert">{{ $message }}</div>@enderror

    <dl class="stats-strip">
        <div><dt>Total pesanan</dt><dd>{{ $user->orders_count }}</dd></div>
        <div><dt>Sedang diproses</dt><dd>{{ $stats['active'] }}</dd></div>
        <div><dt>Total belanja</dt><dd>Rp{{ number_format($stats['spent'], 0, ',', '.') }}</dd></div>
        <div><dt>Terdaftar</dt><dd class="stats-text">{{ $user->created_at->translatedFormat('M Y') }}</dd></div>
    </dl>

    <div class="order-detail-grid">
        <section class="order-products">
            <div class="section-heading"><h2>Pesanan terakhir</h2><span>{{ $orders->count() }} ditampilkan</span></div>
            @forelse ($orders as $order)
                <div class="order-product-row">
                    <div>
                        <span>{{ $order->ordered_at->format('d M Y') }}</span>
                        <strong><a class="text-link" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></strong>
                    </div>
                    <span>{{ $order->items_count }} barang</span>
                    <b>Rp{{ number_format($order->total, 0, ',', '.') }}</b>
                </div>
            @empty
                <p class="chart-empty">Akun ini belum memiliki pesanan.</p>
            @endforelse
        </section>

        <aside class="delivery-data">
            <h2>Data akun</h2>
            <dl>
                <div><dt>Username</dt><dd>{{ $user->username }}</dd></div>
                <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                <div><dt>Peran</dt><dd>{{ $user->is_admin ? 'Admin' : 'Pelanggan' }}</dd></div>
                <div><dt>Terdaftar</dt><dd>{{ $user->created_at->translatedFormat('d F Y') }}</dd></div>
            </dl>
            <form class="role-form" method="POST" action="{{ route('admin.users.role', $user) }}">
                @csrf
                @method('PATCH')
                <label for="is_admin">Ubah peran akun</label>
                <div>
                    <select id="is_admin" name="is_admin" @disabled($user->is(auth()->user()))>
                        <option value="0" @selected(! $user->is_admin)>Pelanggan</option>
                        <option value="1" @selected($user->is_admin)>Admin</option>
                    </select>
                    <button type="submit" @disabled($user->is(auth()->user()))>Simpan</button>
                </div>
                @if ($user->is(auth()->user()))
                    <small class="form-note">Peran akun Anda sendiri tidak dapat diubah dari halaman ini.</small>
                @endif
            </form>
        </aside>
    </div>
</div>
@endsection
