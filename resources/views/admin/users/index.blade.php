@extends('layouts.admin')

@section('title', 'Pengguna Admin — '.config('app.name'))
@section('heading', 'Pengguna')

@section('content')
@php
    $cards = [
        ['Total akun', $summary['total'], 'i-users', 'text-brand-600 bg-brand-50'],
        ['Admin', $summary['admins'], 'i-shield', 'text-accent-600 bg-accent-500/15'],
        ['Pelanggan', $summary['customers'], 'i-user', 'text-success-600 bg-success-500/15'],
        ['Hasil filter', $users->total(), 'i-search', 'text-ink-600 bg-ink-100'],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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

<form method="GET" class="relative mt-5 sm:max-w-xs">
    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
    <input name="q" value="{{ $search }}" placeholder="Cari username atau email"
           class="w-full rounded-xl border border-ink-200 bg-white py-2.5 pl-10 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
</form>

<section class="mt-5 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Pengguna</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Peran</th>
                    <th class="px-5 py-3 text-center">Pesanan</th>
                    <th class="px-5 py-3">Terdaftar</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($users as $user)
                    <tr class="transition hover:bg-ink-50/60">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ink-900 text-xs font-bold text-white">
                                    {{ $user->initials() }}
                                </span>
                                <strong class="font-semibold text-ink-900">{{ $user->username }}</strong>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-ink-600">{{ $user->email }}</td>
                        <td class="px-5 py-3.5">
                            @if ($user->is_admin)
                                <span class="inline-flex items-center rounded-lg bg-brand-100 px-2.5 py-1 text-xs font-bold text-brand-800">Admin</span>
                            @else
                                <span class="inline-flex items-center rounded-lg bg-success-500/15 px-2.5 py-1 text-xs font-bold text-success-600">Pelanggan</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center font-bold text-ink-700">{{ $user->orders_count }}</td>
                        <td class="px-5 py-3.5 text-ink-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-ink-400">Pengguna tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($users->hasPages())
    <div class="mt-6">{{ $users->links() }}</div>
@endif
@endsection
