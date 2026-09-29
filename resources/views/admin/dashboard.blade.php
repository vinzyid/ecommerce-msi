@extends('layouts.app')

@section('title', 'Admin — '.config('app.name'))

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

    @php
        $categoryMax = max(1, (int) $productsByCategory->max('products_count'));
        $statusMeta = [
            'pending' => ['label' => 'Menunggu', 'color' => '#c99a2e'],
            'processing' => ['label' => 'Diproses', 'color' => '#3557a8'],
            'shipped' => ['label' => 'Dikirim', 'color' => '#16305c'],
            'completed' => ['label' => 'Selesai', 'color' => '#2f7d5b'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => '#b23c34'],
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
            $donutSegments[] = $statusMeta[$row['status']]['color'].' '.round($cursor, 2).'% '.round($cursor + $share, 2).'%';
            $cursor += $share;
        }

        $revenueMax = max(1, (int) $revenueByMonth->max('total'));
        $revenueTotal = (int) $revenueByMonth->sum('total');
    @endphp

    <section class="admin-section chart-section">
        <div class="section-heading"><h2>Statistik toko</h2><span>6 bulan terakhir</span></div>
        <div class="chart-grid">
            <article class="chart-card">
                <header><h3>Produk per kategori</h3><span>{{ $stats['products'] }} produk</span></header>
                <div class="bar-chart">
                    @forelse ($productsByCategory as $category)
                        <div class="bar-row">
                            <span class="bar-label">{{ $category->name }}</span>
                            <span class="bar-track">
                                <span class="bar-fill" style="width: {{ round($category->products_count / $categoryMax * 100, 1) }}%"></span>
                            </span>
                            <b class="bar-value">{{ $category->products_count }}</b>
                        </div>
                    @empty
                        <p class="chart-empty">Belum ada kategori.</p>
                    @endforelse
                </div>
            </article>

            <article class="chart-card">
                <header><h3>Pendapatan bulanan</h3><span>Total Rp{{ number_format($revenueTotal, 0, ',', '.') }}</span></header>
                <div class="column-chart" role="img" aria-label="Pendapatan enam bulan terakhir">
                    @foreach ($revenueByMonth as $month)
                        <div class="column-item">
                            <span class="column-value">Rp{{ number_format($month['total'] / 1000, 0, ',', '.') }}k</span>
                            <span class="column-track">
                                <span class="column-fill" style="height: {{ round($month['total'] / $revenueMax * 100, 1) }}%"></span>
                            </span>
                            <span class="column-label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="chart-card">
                <header><h3>Status pesanan</h3><span>{{ $ordersByStatus->sum() }} pesanan</span></header>
                @if ($statusOrder->isEmpty())
                    <p class="chart-empty">Belum ada pesanan.</p>
                @else
                    <div class="donut-layout">
                        <div class="donut" style="--segments: {{ implode(', ', $donutSegments) }}">
                            <span class="donut-hole">
                                <strong>{{ $ordersByStatus->sum() }}</strong>
                                <small>pesanan</small>
                            </span>
                        </div>
                        <ul class="donut-legend">
                            @foreach ($statusOrder as $row)
                                <li>
                                    <i class="legend-swatch" style="background: {{ $statusMeta[$row['status']]['color'] }}" aria-hidden="true"></i>
                                    <span>{{ $statusMeta[$row['status']]['label'] }}</span>
                                    <b>{{ $row['total'] }}</b>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </article>
        </div>
    </section>

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
