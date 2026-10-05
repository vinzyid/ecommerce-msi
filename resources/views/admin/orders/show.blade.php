@extends('layouts.admin')

@section('title', 'Kelola '.$order->order_number.' — '.config('app.name'))
@section('heading', $order->order_number)

@section('content')
@php $locked = in_array($order->status, ['completed', 'cancelled'], true); @endphp

<nav class="flex items-center gap-2 text-sm text-ink-500" aria-label="Breadcrumb">
    <a href="{{ route('admin.orders.index') }}" class="hover:text-brand-700">Pesanan</a>
    <span class="text-ink-300">/</span>
    <b class="font-semibold text-ink-700">{{ $order->order_number }}</b>
</nav>

<div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
    <div>
        <span class="text-xs font-semibold text-ink-400">Dibuat {{ $order->ordered_at->translatedFormat('d M Y, H:i') }}</span>
        <div class="mt-1.5 flex flex-wrap items-center gap-3">
            <strong class="text-xl font-extrabold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
            <x-status-badge :status="$order->status" class="!px-3 !py-1.5 !text-sm" />
        </div>
    </div>

    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-end gap-2.5">
        @csrf
        @method('PATCH')
        <div>
            <label for="status" class="block text-xs font-semibold text-ink-500">Ubah status</label>
            <select id="status" name="status" @disabled($locked)
                    class="mt-1 rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm font-semibold outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 disabled:bg-ink-50 disabled:text-ink-400">
                @foreach (\App\Models\Order::STATUSES as $status)
                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" @disabled($locked)
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:bg-ink-200 disabled:text-ink-400">
            Perbarui
        </button>
    </form>
</div>

@if ($locked)
    <p class="mt-3 rounded-xl border border-ink-200 bg-ink-50 px-4 py-3 text-sm font-semibold text-ink-500">
        Pesanan sudah {{ $order->statusLabel() }} dan tidak dapat diubah lagi.
    </p>
@endif
@error('status')<p class="mt-3 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $message }}</p>@enderror

<div class="mt-5 grid gap-5 lg:grid-cols-[1fr_340px]">
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
        <h2 class="text-base font-extrabold text-ink-900">Pelanggan &amp; Pengiriman</h2>
        <dl class="mt-4 space-y-3 text-sm">
            @foreach ([
                'Akun' => $order->user->username.' / '.$order->user->email,
                'Penerima' => $order->customer_name,
                'Telepon' => $order->phone,
                'Alamat' => $order->fullAddress(),
                'Pengiriman' => \App\Support\CartCalculator::shippingLabel($order->shipping_method ?? 'regular'),
                'Pembayaran' => $order->paymentLabel(),
                'Catatan' => $order->notes,
            ] as $label => $value)
                @if ($value)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">{{ $label }}</dt>
                        <dd class="mt-0.5 leading-relaxed font-medium text-ink-700">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>

        <a href="{{ route('admin.orders.index') }}"
           class="mt-5 block rounded-xl border border-ink-200 px-4 py-2.5 text-center text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
            &larr; Semua pesanan
        </a>
    </aside>
</div>
@endsection
