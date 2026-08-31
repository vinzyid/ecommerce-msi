@extends('layouts.app')

@section('title', 'Admin — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')

    <header class="page-heading admin-heading">
        <span class="section-code">ADMIN / RINGKASAN</span>
        <h1>Operasional toko</h1>
    </header>

    <dl class="stats-strip">
        <div><dt>Produk</dt><dd>{{ $stats['products'] }}</dd></div>
        <div><dt>Stok ≤ 5</dt><dd>{{ $stats['low_stock'] }}</dd></div>
        <div><dt>Perlu diproses</dt><dd>{{ $stats['pending_orders'] }}</dd></div>
        <div><dt>Pelanggan</dt><dd>{{ $stats['customers'] }}</dd></div>
    </dl>

    <section class="admin-section">
        <div class="section-heading"><h2>Pesanan terakhir</h2><a href="{{ route('admin.orders.index') }}">Lihat semua</a></div>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->user->username }}</td>
                            <td>{{ $order->ordered_at->format('d M Y, H:i') }}</td>
                            <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                            <td><b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
