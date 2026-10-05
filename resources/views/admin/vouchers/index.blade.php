@extends('layouts.admin')

@section('title', 'Voucher Admin — '.config('app.name'))
@section('heading', 'Voucher')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="relative min-w-0 flex-1 sm:max-w-xs">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
        <input name="q" value="{{ $search }}" placeholder="Cari kode voucher"
               class="w-full rounded-xl border border-ink-200 bg-white py-2.5 pl-10 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
    </form>

    <a href="{{ route('admin.vouchers.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><use href="#i-plus"/></svg>
        Tambah voucher
    </a>
</div>

@if ($errors->any())
    <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $errors->first() }}</div>
@endif

<section class="mt-5 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Kode</th>
                    <th class="px-5 py-3">Jenis</th>
                    <th class="px-5 py-3">Nilai</th>
                    <th class="px-5 py-3">Minimum</th>
                    <th class="px-5 py-3 text-center">Pemakaian</th>
                    <th class="px-5 py-3">Berlaku sampai</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($vouchers as $voucher)
                    <tr class="transition hover:bg-ink-50/60">
                        <td class="px-5 py-3.5">
                            <code class="rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-black tracking-wide text-brand-700">{{ $voucher->code }}</code>
                            <small class="mt-1 block text-xs text-ink-400">{{ $voucher->description }}</small>
                        </td>
                        <td class="px-5 py-3.5 text-ink-600">{{ $voucher->type === 'percent' ? 'Persen' : 'Potongan tetap' }}</td>
                        <td class="px-5 py-3.5 font-semibold text-ink-800">
                            {{ $voucher->type === 'percent'
                                ? $voucher->value.'%'.($voucher->max_discount ? ' (maks Rp'.number_format($voucher->max_discount, 0, ',', '.').')' : '')
                                : 'Rp'.number_format($voucher->value, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-ink-600">{{ $voucher->min_spend > 0 ? 'Rp'.number_format($voucher->min_spend, 0, ',', '.') : '—' }}</td>
                        <td class="px-5 py-3.5 text-center font-bold text-ink-700">
                            {{ $voucher->used_count }}{{ $voucher->usage_limit ? ' / '.$voucher->usage_limit : '' }}
                        </td>
                        <td class="px-5 py-3.5 text-ink-500">{{ $voucher->expires_at?->format('d M Y') ?? 'Tanpa batas' }}</td>
                        <td class="px-5 py-3.5">
                            @if ($voucher->isUsable())
                                <span class="inline-flex items-center rounded-lg bg-success-500/15 px-2.5 py-1 text-xs font-bold text-success-600">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-lg bg-danger-500/15 px-2.5 py-1 text-xs font-bold text-danger-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.vouchers.edit', $voucher) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pencil"/></svg>
                                Ubah
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-ink-400">Voucher tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($vouchers->hasPages())
    <div class="mt-6">{{ $vouchers->links() }}</div>
@endif
@endsection
