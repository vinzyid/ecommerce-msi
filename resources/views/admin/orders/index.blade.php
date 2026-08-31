@extends('layouts.app')

@section('title', 'Pesanan Admin — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading"><span class="section-code">ADMIN / PESANAN</span><h1>Semua pesanan</h1></header>

    <nav class="status-filter">
        <a href="{{ route('admin.orders.index') }}" @class(['active' => ! $status])>Semua</a>
        @foreach (\App\Models\Order::STATUSES as $value)
            <a href="{{ route('admin.orders.index', ['status' => $value]) }}" @class(['active' => $status === $value])>{{ ucfirst($value) }}</a>
        @endforeach
    </nav>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
                        <td>{{ $order->user->username }}</td>
                        <td>{{ $order->ordered_at->format('d M Y, H:i') }}</td>
                        <td>{{ $order->items_count }}</td>
                        <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td><b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada pesanan pada status ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
