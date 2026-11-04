@extends('layouts.admin')

@section('title', 'EIS '.$month->translatedFormat('F Y').' — '.config('app.name'))
@section('heading', 'Rincian '.$month->translatedFormat('F Y'))

@section('content')
@php
    $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
    $tiles = $summary ? [
        ['Pendapatan', $rupiah($summary->revenue)],
        ['Pesanan', number_format($summary->orders_count, 0, ',', '.')],
        ['Dibatalkan', number_format($summary->cancelled_count, 0, ',', '.')],
        ['Barang terjual', number_format($summary->items_sold, 0, ',', '.')],
        ['Pelanggan aktif', number_format($summary->active_customers, 0, ',', '.')],
    ] : [];
@endphp

<a href="{{ route('admin.eis.index') }}" class="text-xs font-medium text-brand-600 hover:underline">&lsaquo; Kembali ke EIS</a>

<h2 class="mt-4 text-sm font-semibold text-ink-900">Rekap bulan ini <span class="font-normal text-ink-500">dari database EIS</span></h2>
@if ($tiles)
    <div class="mt-2 grid gap-3 sm:grid-cols-3 xl:grid-cols-5">
        @foreach ($tiles as [$label, $value])
            <div class="rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                <p class="text-xs text-ink-500">{{ $label }}</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-ink-900">{{ $value }}</p>
            </div>
        @endforeach
    </div>
@else
    <p class="mt-2 rounded-2xl border border-ink-100 bg-white p-5 text-sm text-ink-500 shadow-card">Belum ada rekap untuk bulan ini.</p>
@endif

<div class="mt-5 grid gap-4 xl:grid-cols-[1fr_1.5fr]">
    <section class="min-w-0 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">Penjualan produk <span class="font-normal text-ink-500">dari database EIS</span></h2>
        @forelse ($products as $product)
            <div class="flex items-baseline justify-between gap-3 border-b border-ink-50 py-2 text-sm last:border-0">
                <span class="min-w-0">
                    <span class="block truncate text-ink-900">{{ $product->product_name }}</span>
                    <span class="block text-xs text-ink-500">{{ $product->quantity }} terjual</span>
                </span>
                <span class="shrink-0 tabular-nums text-ink-700">{{ $rupiah($product->sales) }}</span>
            </div>
        @empty
            <p class="mt-3 text-sm text-ink-500">Belum ada penjualan produk.</p>
        @endforelse
    </section>

    <section class="min-w-0 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
        <h2 class="p-5 pb-3 text-sm font-semibold text-ink-900">Daftar pesanan <span class="font-normal text-ink-500">dari database transaksi</span></h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-sm">
                <thead>
                    <tr class="border-y border-ink-100 text-left text-xs text-ink-500">
                        <th class="px-5 py-2 font-medium">Nomor</th>
                        <th class="px-5 py-2 font-medium">Pelanggan</th>
                        <th class="px-5 py-2 font-medium">Tanggal</th>
                        <th class="px-5 py-2 text-right font-medium">Total</th>
                        <th class="px-5 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="tabular-nums">
                    @forelse ($orders as $order)
                        <tr class="border-b border-ink-50 last:border-0">
                            <td class="px-5 py-2.5"><a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 hover:underline">{{ $order->order_number }}</a></td>
                            <td class="px-5 py-2.5">{{ $order->customer_name }}</td>
                            <td class="px-5 py-2.5 text-ink-500">{{ $order->ordered_at->translatedFormat('d M, H:i') }}</td>
                            <td class="px-5 py-2.5 text-right">{{ $rupiah($order->total) }}</td>
                            <td class="px-5 py-2.5"><x-status-badge :status="$order->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-ink-500">Tidak ada pesanan pada bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="border-t border-ink-100 p-4">{{ $orders->links() }}</div>
        @endif
    </section>
</div>
@endsection
