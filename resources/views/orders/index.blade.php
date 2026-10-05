@extends('layouts.app')

@section('title', 'Pesanan — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-4xl">
        <header>
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Akun / Pesanan</span>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">Riwayat Pesanan</h1>
            <p class="mt-1 text-sm text-ink-500">Pantau status semua transaksi kamu di sini.</p>
        </header>

        <div class="mt-6 space-y-3">
            @forelse ($orders as $order)
                <a href="{{ route('orders.show', $order) }}"
                   class="flex flex-wrap items-center gap-x-6 gap-y-3 rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition hover:border-brand-200 hover:shadow-lift">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <strong class="text-sm font-extrabold text-ink-900">{{ $order->order_number }}</strong>
                            <x-status-badge :status="$order->status" />
                        </div>
                        <p class="mt-1.5 text-xs text-ink-500">
                            {{ $order->ordered_at->translatedFormat('d M Y, H:i') }} &bull; {{ $order->items_count }} barang
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-ink-400">Total</div>
                        <div class="text-base font-extrabold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</div>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow-right"/></svg>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ink-50 text-ink-400">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg>
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-ink-900">Belum ada pesanan</h2>
                    <p class="mt-1 text-sm text-ink-500">Pesanan yang selesai di-checkout akan tampil di halaman ini.</p>
                    <a href="{{ route('home') }}" class="mt-5 inline-block rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">Buka katalog</a>
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
