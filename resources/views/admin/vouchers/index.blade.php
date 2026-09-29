@extends('layouts.app')

@section('title', 'Voucher Admin — '.config('app.name'))

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading heading-with-action">
        <div><span class="section-code">ADMIN / VOUCHER</span><h1>Daftar voucher</h1></div>
        <a class="primary-button button-link fit" href="{{ route('admin.vouchers.create') }}">Tambah voucher</a>
    </header>

    <form class="admin-search" method="GET">
        <input name="q" value="{{ $search }}" placeholder="Cari kode voucher">
        <button type="submit">Cari</button>
    </form>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Jenis</th><th>Nilai</th><th>Minimum</th><th>Pemakaian</th><th>Berlaku sampai</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($vouchers as $voucher)
                    <tr>
                        <td><code>{{ $voucher->code }}</code><small>{{ $voucher->description }}</small></td>
                        <td>{{ $voucher->type === 'percent' ? 'Persen' : 'Potongan tetap' }}</td>
                        <td>{{ $voucher->type === 'percent' ? $voucher->value.'%'.($voucher->max_discount ? ' (maks Rp'.number_format($voucher->max_discount, 0, ',', '.').')' : '') : 'Rp'.number_format($voucher->value, 0, ',', '.') }}</td>
                        <td>{{ $voucher->min_spend > 0 ? 'Rp'.number_format($voucher->min_spend, 0, ',', '.') : '-' }}</td>
                        <td>{{ $voucher->used_count }}{{ $voucher->usage_limit ? ' / '.$voucher->usage_limit : '' }}</td>
                        <td>{{ $voucher->expires_at?->format('d M Y') ?? 'Tanpa batas' }}</td>
                        <td><b class="status {{ $voucher->isUsable() ? 'status-completed' : 'status-cancelled' }}">{{ $voucher->isUsable() ? 'Aktif' : 'Nonaktif' }}</b></td>
                        <td><a href="{{ route('admin.vouchers.edit', $voucher) }}">Ubah</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">Voucher tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($vouchers->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($vouchers->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $vouchers->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $vouchers->currentPage() }} dari {{ $vouchers->lastPage() }}</b>
            @if ($vouchers->hasMorePages())<a href="{{ $vouchers->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</div>
@endsection
