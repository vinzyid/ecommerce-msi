@extends('layouts.admin')

@section('title', 'Chatbot AI — '.config('app.name'))
@section('heading', 'Chatbot AI')

@section('content')
@php
    $cards = [
        ['Pertanyaan unik', number_format($stats['total_entries'], 0, ',', '.'), 'i-receipt', 'text-brand-600 bg-brand-50'],
        ['Dijawab dari cache', number_format($stats['total_hits'], 0, ',', '.'), 'i-refresh', 'text-success-600 bg-success-500/15'],
        ['Hit rate', $stats['hit_rate'].'%', 'i-chart', 'text-accent-600 bg-accent-500/15'],
        ['Token dihemat', number_format($stats['tokens_saved'], 0, ',', '.'), 'i-bolt', 'text-emerald-600 bg-emerald-50'],
    ];
@endphp

{{-- Banner GREEN COMPUTING --}}
<div class="overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/60 p-5 shadow-card">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-sm">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bolt"/></svg>
            </span>
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white">
                    Green Computing
                </span>
                <h2 class="mt-1 text-base font-extrabold text-ink-900 sm:text-lg">Efisiensi Energi &amp; Penghematan Token AI</h2>
                <p class="mt-0.5 text-xs text-ink-500">Pertanyaan yang mirip otomatis dijawab dari cache tanpa memanggil ulang AI, sehingga menghemat listrik &amp; menekan emisi karbon.</p>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-emerald-100 bg-white/80 p-3.5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">Token LLM dihemat</p>
            <p class="mt-1 text-xl font-extrabold text-emerald-700">{{ number_format($stats['tokens_saved'], 0, ',', '.') }}</p>
            <p class="text-[11px] text-ink-400">≈ {{ number_format($stats['total_hits'], 0, ',', '.') }} panggilan AI dihindari</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-white/80 p-3.5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">Energi dihemat</p>
            <p class="mt-1 text-xl font-extrabold text-emerald-700">{{ number_format($stats['kwh_saved'], 4, ',', '.') }} kWh</p>
            <p class="text-[11px] text-ink-400">estimasi daya GPU inference</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-white/80 p-3.5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">Emisi karbon dicegah</p>
            <p class="mt-1 text-xl font-extrabold text-emerald-700">{{ number_format($stats['co2_saved_grams'], 2, ',', '.') }} g CO₂e</p>
            <p class="text-[11px] text-ink-400">faktor grid listrik ~0,8 kg CO₂e/kWh</p>
        </div>
    </div>
</div>

{{-- Info model & TTL --}}
<div class="mt-4 flex flex-wrap items-center gap-2 rounded-2xl border border-brand-100 bg-brand-50/60 px-4 py-3 text-sm">
    <span class="inline-flex items-center gap-2 font-semibold text-brand-800">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bolt"/></svg>
        Model: <code class="rounded-md bg-white px-2 py-0.5 text-xs font-black text-brand-700">{{ $stats['model'] }}</code>
    </span>
    <span class="text-ink-400">•</span>
    <span class="text-ink-600">Cache {{ $stats['cache_ttl'] }} menit</span>
    <span class="text-ink-400">•</span>
    <span class="text-ink-600">{{ $stats['fresh_entries'] }} aktif, {{ $stats['stale_entries'] }} kedaluwarsa</span>
</div>

{{-- Kartu statistik --}}
<div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as [$label, $value, $icon, $tone])
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

{{-- Ringkasan penghematan --}}
<div class="mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-success-500/20 bg-success-500/10 px-4 py-3 text-sm text-success-700">
    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bolt"/></svg>
    <p>
        <b>{{ number_format($stats['total_hits'], 0, ',', '.') }} pertanyaan</b> (termasuk pertanyaan mirip) dijawab dari cache,
        hemat <b>{{ number_format($stats['tokens_saved'], 0, ',', '.') }} token</b>,
        <b>{{ number_format($stats['kwh_saved'], 4, ',', '.') }} kWh</b> listrik, dan
        <b>{{ number_format($stats['co2_saved_grams'], 2, ',', '.') }} g CO₂e</b>.
    </p>
</div>

{{-- Aksi --}}
<div class="mt-5 flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="relative min-w-0 flex-1 sm:max-w-xs">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
        <input name="q" value="{{ $search }}" placeholder="Cari pertanyaan"
               class="w-full rounded-xl border border-ink-200 bg-white py-2.5 pl-10 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
    </form>

    @if ($stats['total_entries'] > 0)
        <form method="POST" action="{{ route('admin.chatbot.purge') }}" onsubmit="return confirm('Bersihkan SEMUA cache chatbot? Pertanyaan berikutnya akan memanggil AI lagi.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-danger-500/30 px-4 py-2.5 text-sm font-bold text-danger-600 transition hover:bg-danger-500/10">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                Bersihkan semua cache
            </button>
        </form>
    @endif
</div>

{{-- Tabel cache --}}
<section class="mt-5 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Pertanyaan</th>
                    <th class="px-5 py-3">Penanya</th>
                    <th class="px-5 py-3 text-center">Dipakai ulang</th>
                    <th class="px-5 py-3">Status cache</th>
                    <th class="px-5 py-3">Terakhir</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($messages as $message)
                    <tr class="align-top transition hover:bg-ink-50/60">
                        <td class="max-w-md px-5 py-3.5">
                            <p class="font-semibold text-ink-800">{{ $message->question }}</p>
                            @if ($message->canonical_key)
                                <p class="mt-1 flex items-center gap-1 text-[11px] text-emerald-700">
                                    <span class="rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[10px] font-bold">Kunci: {{ $message->canonical_key }}</span>
                                </p>
                            @endif
                            <p class="mt-1 line-clamp-2 text-xs text-ink-500">{{ \Illuminate\Support\Str::limit($message->answer, 140) }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($message->user)
                                <a href="{{ route('admin.users.show', $message->user) }}" class="font-semibold text-brand-700 hover:underline">{{ $message->user->username }}</a>
                            @else
                                <span class="text-ink-400">Tamu</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-success-500/15 px-2 py-1 text-xs font-bold text-success-600">
                                {{ $message->hit_count }}×
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($message->isFresh())
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-success-500/15 px-2.5 py-1 text-xs font-bold text-success-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><use href="#i-check-circle"/></svg>
                                    Aktif
                                </span>
                                <small class="mt-1 block text-[11px] text-ink-400">sampai {{ $message->expires_at->format('d M H:i') }}</small>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-ink-100 px-2.5 py-1 text-xs font-bold text-ink-500">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><use href="#i-clock"/></svg>
                                    Kedaluwarsa
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-ink-500">{{ $message->updated_at->diffForHumans() }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <form method="POST" action="{{ route('admin.chatbot.destroy', $message) }}" onsubmit="return confirm('Hapus entri cache ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600 transition hover:border-danger-300 hover:text-danger-600">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-ink-400">
                            Belum ada pertanyaan yang tersimpan. Cache akan terisi otomatis saat konsumen memakai chatbot.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($messages->hasPages())
    <div class="mt-6">{{ $messages->links() }}</div>
@endif
@endsection
