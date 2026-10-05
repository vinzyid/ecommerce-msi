@extends('layouts.app')

@section('title', 'Akun — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-4xl">
        <header class="overflow-hidden rounded-2xl bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-6 text-white sm:p-8">
            <span class="text-xs font-bold uppercase tracking-wide text-brand-300">Akun / Data Pelanggan</span>
            <div class="mt-4 flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-lg font-black ring-1 ring-white/20">
                    {{ $user->initials() }}
                </span>
                <div class="min-w-0">
                    <h1 class="text-xl font-extrabold tracking-tight sm:text-2xl">Halo, {{ $user->username }}.</h1>
                    <p class="mt-0.5 truncate text-sm text-white/70">{{ $user->email }} &middot; {{ $user->is_admin ? 'Admin' : 'Pelanggan' }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-white/60">Kelola data diri, pantau belanja, dan telusuri status pesanan dari satu halaman.</p>
        </header>

        <section class="mt-5 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan aktivitas akun">
            @foreach ([
                ['Total pesanan', $stats['total'], 'semua transaksi'],
                ['Sedang diproses', $stats['active'], 'menunggu sampai dikirim'],
                ['Total belanja', 'Rp'.number_format($stats['spent'], 0, ',', '.'), 'pesanan diselesaikan'],
            ] as [$label, $value, $note])
                <a href="{{ route('orders.index') }}" class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition hover:border-brand-200 hover:shadow-lift">
                    <span class="text-xs font-semibold text-ink-500">{{ $label }}</span>
                    <strong class="mt-1.5 block text-2xl font-extrabold tracking-tight text-ink-900">{{ $value }}</strong>
                    <small class="mt-0.5 block text-xs text-ink-400">{{ $note }}</small>
                </a>
            @endforeach
        </section>

        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_320px]">
            <section class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-ink-900">Pesanan Terakhir</h2>
                    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Lihat semua</a>
                </div>

                <div class="space-y-2.5">
                    @forelse ($orders as $order)
                        <a href="{{ route('orders.show', $order) }}"
                           class="flex items-center gap-4 rounded-xl border border-ink-100 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50/40">
                            <div class="min-w-0 flex-1">
                                <strong class="block truncate text-sm font-bold text-ink-900">{{ $order->order_number }}</strong>
                                <span class="text-xs text-ink-500">{{ $order->ordered_at->translatedFormat('d M Y') }} &bull; {{ $order->items_count }} barang</span>
                            </div>
                            <strong class="shrink-0 text-sm font-extrabold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                            <x-status-badge :status="$order->status" />
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-ink-200 py-10 text-center">
                            <p class="font-semibold text-ink-700">Belum ada pesanan</p>
                            <p class="mt-1 text-sm text-ink-500">Pesanan yang selesai di-checkout akan tampil di sini.</p>
                            <a href="{{ route('home') }}" class="mt-4 inline-block rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Buka katalog</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="space-y-4">
                <section class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-ink-900">Data Akun</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-success-500/10 px-2 py-1 text-[11px] font-bold text-success-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span> Aktif
                        </span>
                    </div>
                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach ([
                            'Username' => $user->username,
                            'Email' => $user->email,
                            'Jenis akun' => $user->is_admin ? 'Admin' : 'Pelanggan',
                            'Terdaftar' => $user->created_at->translatedFormat('d M Y'),
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">{{ $label }}</dt>
                                <dd class="mt-0.5 truncate font-semibold text-ink-800">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section class="space-y-2.5" aria-label="Aksi akun">
                    <a href="{{ route('cart.index') }}"
                       class="flex items-center justify-between rounded-xl border border-ink-200 bg-white px-4 py-3 text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                        Keranjang belanja
                        <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-bold text-white">{{ $cartCount }}</span>
                    </a>
                    @if ($user->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                           class="block rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-center text-sm font-bold text-brand-700 transition hover:bg-brand-100">
                            Buka panel admin
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-xl border border-ink-200 bg-white px-4 py-3 text-sm font-semibold text-danger-600 transition hover:border-danger-300 hover:bg-danger-500/5">
                            Keluar dari akun
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
