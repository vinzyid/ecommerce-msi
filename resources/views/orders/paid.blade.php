@extends('layouts.app')

@section('title', 'Pembayaran berhasil — '.config('app.name'))

@section('content')
<div class="container-page py-14 sm:py-20">
    <div class="mx-auto max-w-md text-center">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-success-500/15 text-success-600">
            <svg class="h-9 w-9" aria-hidden="true"><use href="#i-check-circle"/></svg>
        </span>
        <h1 class="mt-5 text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">Pembayaran Berhasil</h1>
        <p class="mt-2 text-sm text-ink-500">Terima kasih! Pesananmu sedang dikemas oleh penjual.</p>

        <dl class="mt-7 space-y-2.5 rounded-2xl border border-ink-100 bg-white p-5 text-left text-sm shadow-card">
            <div class="flex justify-between gap-4">
                <dt class="text-ink-500">Nomor pesanan</dt>
                <dd class="font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-ink-500">Metode</dt>
                <dd class="font-medium text-ink-900">{{ $order->paymentLabel() }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-t border-ink-100 pt-2.5">
                <dt class="font-medium text-ink-700">Total dibayar</dt>
                <dd class="font-semibold tabular-nums text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <div class="mt-6 grid gap-2.5 sm:grid-cols-2">
            <a href="{{ route('orders.show', $order) }}" class="rounded-full bg-ink-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-ink-700">Lihat Pesanan</a>
            <a href="{{ route('home') }}" class="rounded-full border border-ink-200 px-5 py-3 text-sm font-medium text-ink-800 transition hover:border-ink-900">Belanja Lagi</a>
        </div>
    </div>
</div>
@endsection
