@extends('layouts.app')

@section('title', $order->order_number.' — Etalase')

@section('content')
<div class="page-shell narrow-shell">
    <div class="breadcrumb"><a href="{{ route('orders.index') }}">Pesanan</a><span>/</span><b>{{ $order->order_number }}</b></div>

    <header class="order-detail-heading">
        <div>
            <span class="section-code">PESANAN / {{ $order->ordered_at->format('d.m.Y') }}</span>
            <h1>{{ $order->order_number }}</h1>
        </div>
        <b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b>
    </header>

    <div class="order-detail-grid">
        <section class="order-products">
            <div class="section-heading"><h2>Rincian barang</h2><span>{{ $order->items->sum('quantity') }} barang</span></div>
            @foreach ($order->items as $item)
                <div class="order-product-row">
                    <div><span>{{ $item->sku }}</span><strong>{{ $item->product_name }}</strong></div>
                    <span>{{ $item->quantity }} × Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                    <b>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</b>
                </div>
            @endforeach
            <dl class="order-totals">
                <div><dt>Subtotal</dt><dd>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                <div><dt>Ongkir</dt><dd>{{ (int) $order->shipping_cost === 0 ? 'Gratis' : 'Rp'.number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                <div><dt>Total</dt><dd>Rp{{ number_format($order->total, 0, ',', '.') }}</dd></div>
            </dl>
        </section>

        <aside class="delivery-data">
            <h2>Pengiriman</h2>
            <dl>
                <div><dt>Penerima</dt><dd>{{ $order->customer_name }}</dd></div>
                <div><dt>Telepon</dt><dd>{{ $order->phone }}</dd></div>
                <div><dt>Alamat</dt><dd>{{ $order->address }}</dd></div>
                <div><dt>Pembayaran</dt><dd>{{ $order->paymentLabel() }}</dd></div>
                @if ($order->notes)<div><dt>Catatan</dt><dd>{{ $order->notes }}</dd></div>@endif
            </dl>
        </aside>
    </div>
</div>
@endsection
