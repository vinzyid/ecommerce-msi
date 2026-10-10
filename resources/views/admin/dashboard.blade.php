@extends('layouts.admin')

@section('title', 'Ringkasan — '.config('app.name'))
@section('heading', 'Ringkasan')

@section('content')
@php
    $categoryMax = max(1, (int) $productsByCategory->max('products_count'));
    $statusMeta = [
        'pending' => ['label' => 'Menunggu', 'class' => 'bg-accent-500', 'text' => 'text-accent-600'],
        'processing' => ['label' => 'Diproses', 'class' => 'bg-brand-500', 'text' => 'text-brand-700'],
        'shipped' => ['label' => 'Dikirim', 'class' => 'bg-brand-800', 'text' => 'text-brand-900'],
        'completed' => ['label' => 'Selesai', 'class' => 'bg-success-500', 'text' => 'text-success-600'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-danger-500', 'text' => 'text-danger-600'],
    ];
    $statusTotal = max(1, (int) $ordersByStatus->sum());
    $statusOrder = collect(array_keys($statusMeta))
        ->map(fn ($status) => ['status' => $status, 'total' => (int) $ordersByStatus->get($status, 0)])
        ->filter(fn ($row) => $row['total'] > 0)
        ->values();

    $cursor = 0.0;
    $donutSegments = [];
    foreach ($statusOrder as $row) {
        $share = $row['total'] / $statusTotal * 100;
        $color = ['pending' => '#ff9500', 'processing' => '#2f83ff', 'shipped' => '#133fa6', 'completed' => '#10b981', 'cancelled' => '#f43f5e'][$row['status']];
        $donutSegments[] = $color.' '.round($cursor, 2).'% '.round($cursor + $share, 2).'%';
        $cursor += $share;
    }

    $revenueMax = max(1, (int) $revenueByMonth->max('total'));
    $revenueTotal = (int) $revenueByMonth->sum('total');

    $statCards = [
        ['Produk aktif', $stats['products'], 'i-box', 'bg-brand-50 text-brand-600'],
        ['Stok rendah (≤5)', $stats['low_stock'], 'i-bolt', 'bg-accent-500/15 text-accent-600'],
        ['Perlu diproses', $stats['pending_orders'], 'i-clock', 'bg-danger-500/15 text-danger-600'],
        ['Pelanggan', $stats['customers'], 'i-users', 'bg-success-500/15 text-success-600'],
    ];
@endphp

{{-- Stat cards --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($statCards as [$label, $value, $icon, $tone])
        <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-ink-500">{{ $label }}</p>
                    <p class="mt-1.5 text-2xl font-extrabold tracking-tight text-ink-900">{{ $value }}</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tone }}">
                    <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#{{ $icon }}"/></svg>
                </span>
            </div>
        </div>
    @endforeach
</div>

{{-- Statistik --}}
<div class="mt-5 grid gap-4 xl:grid-cols-3">
    <article class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-ink-900">Produk per kategori</h2>
            <span class="text-xs font-semibold text-ink-400">{{ $stats['products'] }} produk</span>
        </div>
        <div class="space-y-3">
            @forelse ($productsByCategory as $category)
                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 truncate text-xs font-semibold text-ink-600">{{ $category->name }}</span>
                    <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-ink-100">
                        <span class="block h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700"
                              style="width: {{ round($category->products_count / $categoryMax * 100, 1) }}%"></span>
                    </span>
                    <b class="w-6 shrink-0 text-right text-xs font-bold text-ink-800">{{ $category->products_count }}</b>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-ink-400">Belum ada kategori.</p>
            @endforelse
        </div>
    </article>

    <article class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-ink-900">Pendapatan bulanan</h2>
            <span class="text-xs font-semibold text-ink-400">Rp{{ number_format($revenueTotal, 0, ',', '.') }}</span>
        </div>
        <div class="flex h-48 items-end justify-between gap-2">
            @foreach ($revenueByMonth as $month)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                    <span class="text-[10px] font-bold text-ink-500">Rp{{ number_format($month['total'] / 1000, 0, ',', '.') }}k</span>
                    <span class="flex w-full flex-1 items-end">
                        <span class="w-full rounded-t-lg bg-gradient-to-t from-brand-700 to-brand-400"
                              style="height: {{ round($month['total'] / $revenueMax * 100, 1) }}%"></span>
                    </span>
                    <span class="text-[10px] font-semibold text-ink-400">{{ $month['label'] }}</span>
                </div>
            @endforeach
        </div>
    </article>

    <article class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-ink-900">Status pesanan</h2>
            <span class="text-xs font-semibold text-ink-400">{{ $ordersByStatus->sum() }} pesanan</span>
        </div>

        @if ($statusOrder->isEmpty())
            <p class="py-10 text-center text-sm text-ink-400">Belum ada pesanan.</p>
        @else
            <div class="flex items-center gap-5">
                <div class="relative h-32 w-32 shrink-0 rounded-full"
                     style="background: conic-gradient({{ implode(', ', $donutSegments) }})">
                    <span class="absolute inset-[22%] flex flex-col items-center justify-center rounded-full bg-white">
                        <strong class="text-lg font-extrabold text-ink-900">{{ $ordersByStatus->sum() }}</strong>
                        <small class="text-[10px] text-ink-400">pesanan</small>
                    </span>
                </div>
                <ul class="min-w-0 flex-1 space-y-2">
                    @foreach ($statusOrder as $row)
                        <li class="flex items-center gap-2 text-xs">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $statusMeta[$row['status']]['class'] }}"></span>
                            <span class="flex-1 truncate font-semibold text-ink-600">{{ $statusMeta[$row['status']]['label'] }}</span>
                            <b class="font-bold text-ink-900">{{ $row['total'] }}</b>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </article>
</div>

{{-- Pesanan terakhir --}}
<section class="mt-5 rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="flex items-center justify-between border-b border-ink-100 px-5 py-4">
        <h2 class="text-sm font-extrabold text-ink-900">Pesanan Terakhir</h2>
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
            Lihat semua
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow-right"/></svg>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Nomor</th>
                    <th class="px-5 py-3">Pelanggan</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Total</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($recentOrders as $order)
                    <tr class="transition hover:bg-ink-50/60">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ $order->order_number }}</a>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-ink-700">{{ $order->user->username }}</td>
                        <td class="px-5 py-3.5 text-ink-500">{{ $order->ordered_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-3.5 font-bold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5"><x-status-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-ink-400">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
