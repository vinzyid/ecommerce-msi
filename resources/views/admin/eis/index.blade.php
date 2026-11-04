@extends('layouts.admin')

@section('title', 'EIS Eksekutif — '.config('app.name'))
@section('heading', 'EIS Eksekutif')

@section('content')
@php
    use App\Services\EisReportService;
    $short = fn ($value) => EisReportService::shortRupiah((float) $value);
    $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
@endphp

@if ($unavailable)
    <div class="rounded-2xl border border-ink-100 bg-white p-8 text-center shadow-card">
        <svg class="mx-auto h-10 w-10 text-ink-300" aria-hidden="true"><use href="#i-info"/></svg>
        <h2 class="mt-3 text-lg font-semibold text-ink-900">Database EIS belum siap</h2>
        <p class="mx-auto mt-1 max-w-lg text-sm text-ink-500">Halaman ini memerlukan database kedua. Pastikan koneksinya benar, lalu jalankan:</p>
        <code class="mt-3 inline-block rounded-lg bg-ink-50 px-3 py-2 text-xs text-ink-700">php artisan migrate --database=eis --path=database/migrations/eis</code>
    </div>
@else
    {{-- Filter periode & sinkronisasi --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1 rounded-full bg-white p-1 shadow-xs ring-1 ring-ink-100" role="group" aria-label="Pilih periode">
            @foreach (EisReportService::PERIODS as $value => $label)
                <a href="{{ route('admin.eis.index', ['months' => $value]) }}"
                   @if ($months === $value) aria-current="true" @endif
                   @class([
                       'rounded-full px-3.5 py-1.5 text-xs font-medium transition-colors',
                       'bg-ink-900 text-white' => $months === $value,
                       'text-ink-600 hover:bg-ink-50' => $months !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <form method="POST" action="{{ route('admin.eis.sync') }}" class="flex items-center gap-3">
            @csrf
            <span class="text-xs text-ink-500">
                @if ($eis['lastSync'])
                    Sinkron terakhir {{ $eis['lastSync']->finished_at->translatedFormat('d M Y, H:i') }}
                @else
                    Belum pernah disinkronkan
                @endif
            </span>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 bg-white px-3.5 py-2 text-xs font-medium text-ink-800 hover:border-ink-400">
                <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-refresh"/></svg>
                Sinkronkan sekarang
            </button>
        </form>
    </div>

    @if (! $eis['hasData'])
        <div class="mt-5 rounded-2xl border border-ink-100 bg-white p-10 text-center shadow-card">
            <svg class="mx-auto h-10 w-10 text-ink-300" aria-hidden="true"><use href="#i-chart"/></svg>
            <h2 class="mt-3 text-lg font-semibold text-ink-900">Belum ada data rekap</h2>
            <p class="mt-1 text-sm text-ink-500">Tekan "Sinkronkan sekarang" untuk memindahkan data dari database transaksi.</p>
        </div>
    @else
        <p class="mt-5 text-sm text-ink-600">
            Kinerja <span class="font-medium text-ink-900">{{ $eis['range'] }}</span>,
            dibandingkan dengan {{ $eis['previousRange'] }}.
        </p>

        {{-- Angka utama --}}
        <section class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-5" aria-label="Indikator kinerja utama">
            @foreach ($eis['kpis'] as $kpi)
                <div class="rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                    <p class="text-xs text-ink-500">{{ $kpi['label'] }}</p>
                    <p class="mt-1.5 text-2xl font-semibold tracking-tight tabular-nums text-ink-900">{{ $kpi['value'] }}</p>
                    <p class="mt-1.5 text-xs">
                        @if ($kpi['direction'] === null)
                            <span class="text-ink-400">Belum ada pembanding</span>
                        @elseif ($kpi['direction'] === 'flat')
                            <span class="text-ink-500">Tetap</span>
                        @else
                            <span @class(['font-medium tabular-nums', 'text-success-600' => $kpi['good'], 'text-danger-600' => ! $kpi['good']])>
                                <span aria-hidden="true">{{ $kpi['direction'] === 'up' ? '↑' : '↓' }}</span>
                                {{ $kpi['direction'] === 'up' ? 'Naik' : 'Turun' }} {{ $kpi['change'] }}
                            </span>
                        @endif
                    </p>
                </div>
            @endforeach
        </section>

        {{-- Perlu tindakan --}}
        <section class="mt-4 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">Perlu perhatian</h2>
            @forelse ($eis['actions'] as $action)
                @php
                    $meta = [
                        'critical' => ['i-flame', 'text-danger-600', 'Mendesak'],
                        'warning' => ['i-info', 'text-accent-600', 'Perhatian'],
                        'info' => ['i-clock', 'text-brand-600', 'Informasi'],
                    ][$action['level']];
                @endphp
                <div class="mt-3 flex flex-wrap items-start gap-3 border-t border-ink-100 pt-3 first-of-type:border-0">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $meta[1] }}" aria-hidden="true"><use href="#{{ $meta[0] }}"/></svg>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-ink-900">
                            <span class="mr-1 text-xs font-medium {{ $meta[1] }}">{{ $meta[2] }}</span>
                            {{ $action['title'] }}
                        </p>
                        <p class="mt-0.5 text-xs text-ink-500">{{ $action['detail'] }}</p>
                    </div>
                    <a href="{{ $action['url'] }}" class="shrink-0 text-xs font-medium text-brand-600 hover:underline">{{ $action['cta'] }} &rsaquo;</a>
                </div>
            @empty
                <p class="mt-2 text-sm text-ink-500">Tidak ada temuan yang perlu ditindaklanjuti pada periode ini.</p>
            @endforelse
        </section>

        {{-- Tren --}}
        <section class="mt-4 grid gap-4 lg:grid-cols-2">
            @include('admin.eis._bar-chart', [
                'title' => 'Tren pendapatan',
                'note' => 'Warna muda: bulan berjalan',
                'points' => collect($eis['trend'])->map(fn ($p) => $p + ['value' => $p['revenue'], 'display' => $short($p['revenue'])])->all(),
            ])
            @include('admin.eis._bar-chart', [
                'title' => 'Tren jumlah pesanan',
                'note' => 'Klik batang untuk rincian',
                'points' => collect($eis['trend'])->map(fn ($p) => $p + ['value' => $p['paid'], 'display' => (string) $p['paid']])->all(),
            ])
        </section>

        <details class="mt-3 rounded-2xl border border-ink-100 bg-white px-5 py-3 shadow-card">
            <summary class="cursor-pointer text-xs font-medium text-ink-700">Lihat data tren sebagai tabel</summary>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-left text-xs text-ink-500">
                            <th class="py-2 font-medium">Bulan</th>
                            <th class="py-2 text-right font-medium">Pendapatan</th>
                            <th class="py-2 text-right font-medium">Pesanan</th>
                            <th class="py-2 text-right font-medium">Rata-rata</th>
                            <th class="py-2 text-right font-medium">Dibatalkan</th>
                            <th class="py-2 text-right font-medium">Pelanggan baru</th>
                        </tr>
                    </thead>
                    <tbody class="tabular-nums">
                        @foreach ($eis['trend'] as $row)
                            <tr class="border-b border-ink-50 last:border-0">
                                <td class="py-2"><a href="{{ route('admin.eis.show', $row['key']) }}" class="text-brand-600 hover:underline">{{ $row['fullLabel'] }}</a>@if ($row['partial']) <span class="text-xs text-ink-400">(berjalan)</span>@endif</td>
                                <td class="py-2 text-right">{{ $rupiah($row['revenue']) }}</td>
                                <td class="py-2 text-right">{{ $row['paid'] }}</td>
                                <td class="py-2 text-right">{{ $rupiah($row['aov']) }}</td>
                                <td class="py-2 text-right">{{ $row['cancelled'] }}</td>
                                <td class="py-2 text-right">{{ $row['newCustomers'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>

        {{-- Peringkat --}}
        <section class="mt-4 grid gap-4 lg:grid-cols-[1.5fr_1fr]">
            <article class="min-w-0 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">Produk terlaris</h2>
                <p class="mt-0.5 text-xs text-ink-500">Penjualan dari database EIS, stok terkini dari database transaksi.</p>
                @if ($eis['topProducts']->isEmpty())
                    <p class="mt-4 text-sm text-ink-500">Belum ada penjualan pada periode ini.</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[480px] text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-left text-xs text-ink-500">
                                    <th class="py-2 font-medium">Produk</th>
                                    <th class="py-2 text-right font-medium">Terjual</th>
                                    <th class="py-2 text-right font-medium">Penjualan</th>
                                    <th class="py-2 text-right font-medium">Stok</th>
                                </tr>
                            </thead>
                            <tbody class="tabular-nums">
                                @foreach ($eis['topProducts'] as $product)
                                    <tr class="border-b border-ink-50 last:border-0">
                                        <td class="py-2.5 pr-3">
                                            <span class="block font-medium text-ink-900">{{ $product['name'] }}</span>
                                            <span class="block text-xs text-ink-500">{{ $product['category'] }}</span>
                                        </td>
                                        <td class="py-2.5 text-right">{{ $product['quantity'] }}</td>
                                        <td class="py-2.5 text-right">{{ $short($product['sales']) }}</td>
                                        <td class="py-2.5 text-right">
                                            @if ($product['stock'] === null)
                                                <span class="text-ink-400">Tidak ada</span>
                                            @elseif ($product['stock'] <= EisReportService::LOW_STOCK)
                                                <span class="font-medium text-danger-600">{{ $product['stock'] }} (menipis)</span>
                                            @else
                                                {{ $product['stock'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <article class="min-w-0 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">Penjualan per kategori</h2>
                <p class="mt-0.5 text-xs text-ink-500">Nilai barang, sebelum ongkir dan diskon.</p>
                @forelse ($eis['categories'] as $category)
                    <div class="mt-3">
                        <div class="flex items-baseline justify-between gap-3 text-xs">
                            <span class="min-w-0 truncate text-ink-700">{{ $category['name'] }}</span>
                            <span class="shrink-0 tabular-nums text-ink-500">{{ $short($category['sales']) }} &middot; {{ number_format($category['share'], 0, ',', '.') }}%</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-ink-100">
                            <div class="h-full rounded-full bg-brand-600" style="width: {{ max(1, round($category['share'], 1)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="mt-4 text-sm text-ink-500">Belum ada penjualan pada periode ini.</p>
                @endforelse
            </article>
        </section>

        {{-- Dua database --}}
        <section class="mt-4 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-ink-900">Sumber data</h2>
                @if ($eis['reconciliation']['matches'])
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-success-600">
                        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-check-circle"/></svg>
                        Total kedua database sesuai
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-accent-600">
                        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-info"/></svg>
                        Ada perubahan transaksi sejak sinkronisasi terakhir
                    </span>
                @endif
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($eis['connections'] as $i => $connection)
                    <div class="rounded-xl bg-ink-50 p-4">
                        <p class="text-xs text-ink-500">{{ $connection['title'] }}</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-ink-900">{{ $connection['database'] }}</p>
                        <p class="mt-1 text-xs text-ink-600">{{ $connection['detail'] }}</p>
                        <p class="mt-1 text-xs tabular-nums text-ink-600">
                            Total pendapatan {{ $rupiah($i === 0 ? $eis['reconciliation']['source'] : $eis['reconciliation']['warehouse']) }}
                        </p>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs leading-relaxed text-ink-500">
                Pendapatan dihitung dari total semua pesanan kecuali yang dibatalkan, termasuk pesanan yang masih menunggu pembayaran.
                Perbandingan memakai bulan penuh karena bulan berjalan belum selesai.
                Halaman ini disimpan hingga {{ EisReportService::CACHE_MINUTES }} menit dan diperbarui setiap sinkronisasi.
            </p>
        </section>
    @endif
@endif
@endsection
