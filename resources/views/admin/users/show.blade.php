@extends('layouts.admin')

@section('title', 'Pengguna '.$user->username.' — '.config('app.name'))
@section('heading', $user->username)

@section('content')
@php $isSelf = $user->is(auth()->user()); @endphp

<nav class="flex items-center gap-2 text-sm text-ink-500" aria-label="Breadcrumb">
    <a href="{{ route('admin.users.index') }}" class="hover:text-brand-700">Pengguna</a>
    <span class="text-ink-300">/</span>
    <b class="font-semibold text-ink-700">{{ $user->username }}</b>
</nav>

<div class="mt-4 flex flex-wrap items-center gap-4 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-ink-900 text-lg font-black text-white">
        {{ $user->initials() }}
    </span>
    <div class="min-w-0">
        <h2 class="text-lg font-extrabold text-ink-900">{{ $user->username }}</h2>
        <p class="truncate text-sm text-ink-500">{{ $user->email }}</p>
    </div>
    @if ($user->is_admin)
        <span class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-brand-100 px-3 py-1.5 text-xs font-bold text-brand-800">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-shield"/></svg>
            Admin
        </span>
    @else
        <span class="ml-auto inline-flex items-center rounded-lg bg-success-500/15 px-3 py-1.5 text-xs font-bold text-success-600">Pelanggan</span>
    @endif
</div>

@error('is_admin')<p class="mt-3 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $message }}</p>@enderror

<div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['Total pesanan', $user->orders_count],
        ['Sedang diproses', $stats['active']],
        ['Total belanja', 'Rp'.number_format($stats['spent'], 0, ',', '.')],
        ['Terdaftar', $user->created_at->translatedFormat('M Y')],
    ] as [$label, $value])
        <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
            <p class="text-xs font-semibold text-ink-500">{{ $label }}</p>
            <p class="mt-1.5 text-xl font-extrabold tracking-tight text-ink-900">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-[1fr_340px]">
    <section class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-ink-900">Pesanan Terakhir</h2>
            <span class="text-xs font-semibold text-ink-500">{{ $orders->count() }} ditampilkan</span>
        </div>

        <div class="space-y-2.5">
            @forelse ($orders as $order)
                <a href="{{ route('admin.orders.show', $order) }}"
                   class="flex items-center gap-4 rounded-xl border border-ink-100 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50/40">
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate text-sm font-bold text-brand-600">{{ $order->order_number }}</strong>
                        <span class="text-xs text-ink-500">{{ $order->ordered_at->translatedFormat('d M Y') }} &bull; {{ $order->items_count }} barang</span>
                    </div>
                    <strong class="shrink-0 text-sm font-extrabold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                    <x-status-badge :status="$order->status" />
                </a>
            @empty
                <p class="rounded-xl border border-dashed border-ink-200 py-10 text-center text-sm text-ink-400">
                    Akun ini belum memiliki pesanan.
                </p>
            @endforelse
        </div>
    </section>

    <aside class="h-fit space-y-4">
        <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
            <h2 class="text-base font-extrabold text-ink-900">Data Akun</h2>
            <dl class="mt-4 space-y-3 text-sm">
                @foreach ([
                    'Username' => $user->username,
                    'Email' => $user->email,
                    'Peran' => $user->is_admin ? 'Admin' : 'Pelanggan',
                    'Terdaftar' => $user->created_at->translatedFormat('d F Y'),
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">{{ $label }}</dt>
                        <dd class="mt-0.5 truncate font-semibold text-ink-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.users.role', $user) }}"
              class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
            @csrf
            @method('PATCH')
            <h2 class="text-base font-extrabold text-ink-900">Ubah Peran</h2>
            <div class="mt-3">
                <label for="is_admin" class="block text-xs font-semibold text-ink-500">Peran akun</label>
                <select id="is_admin" name="is_admin" @disabled($isSelf)
                        class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm font-semibold outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 disabled:bg-ink-50 disabled:text-ink-400">
                    <option value="0" @selected(! $user->is_admin)>Pelanggan</option>
                    <option value="1" @selected($user->is_admin)>Admin</option>
                </select>
            </div>
            <button type="submit" @disabled($isSelf)
                    class="mt-3 w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:bg-ink-200 disabled:text-ink-400">
                Simpan peran
            </button>
            @if ($isSelf)
                <p class="mt-2.5 text-xs text-ink-400">Kamu tidak bisa mengubah peran akun sendiri di sini.</p>
            @endif
        </form>

        <a href="{{ route('admin.users.index') }}"
           class="block rounded-xl border border-ink-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
            &larr; Semua pengguna
        </a>
    </aside>
</div>
@endsection
