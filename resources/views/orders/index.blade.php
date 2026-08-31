@extends('layouts.app')

@section('title', 'Pesanan — Etalase')

@section('content')
<div class="page-shell narrow-shell">
    <header class="page-heading">
        <span class="section-code">AKUN / PESANAN</span>
        <h1>Riwayat pesanan</h1>
    </header>

    <div class="order-list">
        @forelse ($orders as $order)
            <a class="order-row" href="{{ route('orders.show', $order) }}">
                <div><span>Nomor</span><strong>{{ $order->order_number }}</strong></div>
                <div><span>Tanggal</span><strong>{{ $order->ordered_at->format('d M Y') }}</strong></div>
                <div><span>Barang</span><strong>{{ $order->items_count }}</strong></div>
                <div><span>Total</span><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></div>
                <div><span>Status</span><b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b></div>
            </a>
        @empty
            <div class="empty-state bordered">
                <h2>Belum ada pesanan</h2>
                <p>Pesanan yang selesai di-checkout akan tampil di halaman ini.</p>
                <a class="text-link" href="{{ route('home') }}">Buka katalog</a>
            </div>
        @endforelse
    </div>

    @if ($orders->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($orders->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $orders->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $orders->currentPage() }} dari {{ $orders->lastPage() }}</b>
            @if ($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</div>
@endsection
