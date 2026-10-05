@extends('layouts.app')

@section('title', $order->order_number.' — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-4xl">
        <nav class="flex items-center gap-2 text-sm text-ink-500" aria-label="Breadcrumb">
            <a href="{{ route('orders.index') }}" class="hover:text-brand-700">Pesanan</a>
            <span class="text-ink-300">/</span>
            <b class="font-semibold text-ink-700">{{ $order->order_number }}</b>
        </nav>

        <header class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Pesanan / {{ $order->ordered_at->format('d.m.Y') }}</span>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink-900">{{ $order->order_number }}</h1>
            </div>
            <x-status-badge :status="$order->status" class="!px-3.5 !py-1.5 !text-sm" />
        </header>

        <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_320px]">
            <section class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-ink-900">Rincian Barang</h2>
                    <span class="text-xs font-semibold text-ink-500">{{ $order->items->sum('quantity') }} barang</span>
                </div>

                <div class="divide-y divide-ink-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-4 py-3">
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ $item->sku }}</div>
                                <div class="truncate text-sm font-bold text-ink-900">{{ $item->product_name }}</div>
                                <div class="mt-0.5 text-xs text-ink-500">{{ $item->quantity }} &times; Rp{{ number_format($item->price, 0, ',', '.') }}</div>
                            </div>
                            <b class="shrink-0 text-sm font-extrabold text-ink-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</b>
                        </div>
                    @endforeach
                </div>

                <dl class="mt-4 space-y-2.5 border-t border-ink-100 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Subtotal</dt>
                        <dd class="font-semibold text-ink-800">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                    </div>
                    @if ((int) $order->discount > 0)
                        <div class="flex justify-between text-success-600">
                            <dt class="font-semibold">Diskon {{ $order->voucher_code }}</dt>
                            <dd class="font-bold">-Rp{{ number_format($order->discount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Ongkir</dt>
                        <dd class="font-semibold text-ink-800">{{ (int) $order->shipping_cost === 0 ? 'Gratis' : 'Rp'.number_format($order->shipping_cost, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-end justify-between border-t border-ink-100 pt-4">
                    <span class="text-sm font-semibold text-ink-600">Total</span>
                    <strong class="text-xl font-extrabold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                </div>
            </section>

            <aside class="h-fit rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <h2 class="text-base font-extrabold text-ink-900">Informasi Pengiriman</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Penerima</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Telepon</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ $order->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Alamat</dt>
                        <dd class="mt-0.5 leading-relaxed text-ink-700">{{ $order->fullAddress() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Pengiriman</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ \App\Support\CartCalculator::shippingLabel($order->shipping_method ?? 'regular') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Pembayaran</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ $order->paymentLabel() }}</dd>
                    </div>
                    @if ($order->notes)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Catatan</dt>
                            <dd class="mt-0.5 leading-relaxed text-ink-700">{{ $order->notes }}</dd>
                        </div>
                    @endif
                </dl>

                <a href="{{ route('orders.index') }}" class="mt-5 block rounded-xl border border-ink-200 px-4 py-2.5 text-center text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                    &larr; Semua pesanan
                </a>
            </aside>
        </div>
    </div>
</div>
@endsection
