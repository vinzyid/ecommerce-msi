@extends('layouts.app')

@section('title', 'Pesanan — '.config('app.name'))

@section('content')
@php
    $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
    $tabs = ['' => 'Semua', 'pending' => 'Menunggu', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
    $statusColors = [
        'pending' => 'text-accent-600',
        'processing' => 'text-brand-700',
        'shipped' => 'text-brand-700',
        'completed' => 'text-success-600',
        'cancelled' => 'text-danger-600',
    ];
    $secondaryButton = 'rounded-full border border-ink-200 bg-white px-4 py-2 text-xs font-medium text-ink-800 transition hover:border-ink-900';
    $primaryButton = 'rounded-full bg-ink-900 px-4 py-2 text-xs font-medium text-white transition hover:bg-ink-700';
@endphp

<div class="container-page pt-6">
    <div class="mx-auto max-w-4xl">
        <header>
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Akun / Pesanan</span>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">Riwayat Pesanan</h1>
        </header>

        {{-- Tab status --}}
        <nav class="no-scrollbar -mx-4 mt-5 flex overflow-x-auto border-b border-ink-100 px-4 md:mx-0 md:px-0" aria-label="Filter status pesanan">
            @foreach ($tabs as $value => $label)
                @php $active = ($status ?? '') === $value; @endphp
                <a href="{{ route('orders.index', array_filter(['status' => $value, 'q' => $search])) }}"
                   @if ($active) aria-current="page" @endif
                   @class([
                       '-mb-px shrink-0 border-b-2 px-4 py-3 text-sm transition-colors md:flex-1 md:text-center',
                       'border-ink-900 font-semibold text-ink-900' => $active,
                       'border-transparent text-ink-500 hover:text-ink-900' => ! $active,
                   ])>
                    {{ $label }}
                    @if ($value !== '' && ($counts[$value] ?? 0) > 0)
                        <span class="ml-0.5 text-xs text-ink-400">({{ $counts[$value] }})</span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('orders.index') }}" class="mt-4">
            @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <label for="order-search" class="sr-only">Cari pesanan</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="order-search" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Cari nomor pesanan atau nama produk"
                       class="w-full rounded-full border border-ink-100 bg-ink-50 py-2.5 pl-10 pr-4 text-base outline-none transition focus:border-ink-300 focus:bg-white sm:text-sm">
            </div>
        </form>

        @if ($errors->hasAny(['reorder', 'review']) || $errors->has('items.*'))
            <p class="mt-4 rounded-xl bg-danger-500/10 px-4 py-3 text-sm text-danger-600" role="alert">{{ $errors->first() }}</p>
        @endif

        <div class="mt-4 space-y-3">
            @forelse ($orders as $order)
                @php
                    $shownItems = $order->items->take(2);
                    $hiddenCount = $order->items->count() - $shownItems->count();
                    $reviewable = $order->status === 'completed' ? $order->items->filter(fn ($item) => $item->product) : collect();
                    $hasUnreviewed = $reviewable->contains(fn ($item) => ! $reviews->has($item->product_id));
                @endphp
                <article class="rounded-2xl border border-ink-100 bg-white shadow-card">
                    <header class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-b border-ink-100 px-4 py-3 sm:px-5">
                        <div class="flex min-w-0 flex-wrap items-center gap-x-1 text-xs text-ink-500">
                            <x-logo :wordmark="false" class="mr-1 [&>svg]:h-5 [&>svg]:w-5" />
                            <span class="font-semibold text-ink-900">{{ config('app.name') }}</span>
                            <span class="mx-1 text-ink-300">&middot;</span>
                            {{ $order->ordered_at->translatedFormat('d M Y') }}
                            <span class="mx-1 text-ink-300">&middot;</span>
                            <span class="font-mono">{{ $order->order_number }}</span>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wide {{ $statusColors[$order->status] ?? 'text-ink-600' }}">{{ $order->statusLabel() }}</span>
                    </header>

                    <a href="{{ route('orders.show', $order) }}" class="block divide-y divide-ink-100 px-4 transition hover:bg-ink-50/60 sm:px-5">
                        @foreach ($shownItems as $item)
                            <div class="flex gap-3 py-3">
                                <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-xl bg-ink-50 text-xs font-semibold text-ink-400">
                                    @if ($item->product?->image_url)
                                        <img src="{{ $item->product->image_url }}" alt="" loading="lazy" class="h-full w-full object-contain p-1 mix-blend-multiply">
                                    @else
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->product_name, 0, 2)) }}
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-2 text-sm font-medium text-ink-900">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-xs text-ink-500">&times;{{ $item->quantity }}</p>
                                </div>
                                <p class="shrink-0 text-sm tabular-nums text-ink-800">{{ $rupiah($item->price) }}</p>
                            </div>
                        @endforeach
                        @if ($hiddenCount > 0)
                            <p class="py-2.5 text-center text-xs text-ink-500">+{{ $hiddenCount }} barang lainnya</p>
                        @endif
                    </a>

                    <footer class="border-t border-ink-100 px-4 py-3 sm:px-5">
                        <p class="text-right text-sm text-ink-600">
                            Total {{ $order->items->sum('quantity') }} barang:
                            <strong class="ml-1 text-base font-semibold tabular-nums text-ink-900">{{ $rupiah($order->total) }}</strong>
                        </p>
                        <div class="mt-3 flex flex-wrap justify-end gap-2">
                            <a href="{{ route('orders.show', $order) }}" class="{{ $secondaryButton }}">Lihat Detail</a>

                            @if ($order->status === 'pending' && $order->payment_method !== 'cod')
                                <a href="{{ route('orders.show', $order) }}" class="{{ $primaryButton }}">Bayar Sekarang</a>
                            @elseif ($order->status === 'shipped')
                                <form method="POST" action="{{ route('orders.complete', $order) }}"
                                      onsubmit="return confirm('Konfirmasi paket sudah kamu terima?');">
                                    @csrf
                                    <button type="submit" class="{{ $primaryButton }}">Pesanan Diterima</button>
                                </form>
                            @endif

                            @if ($reviewable->isNotEmpty())
                                <button type="button" onclick="document.getElementById('review-order-{{ $order->id }}').showModal()"
                                        class="{{ $hasUnreviewed ? $primaryButton : $secondaryButton }}">{{ $hasUnreviewed ? 'Beri Ulasan' : 'Ubah Ulasan' }}</button>
                            @endif

                            @if (in_array($order->status, ['completed', 'cancelled'], true))
                                <form method="POST" action="{{ route('orders.reorder', $order) }}">
                                    @csrf
                                    <button type="submit" class="{{ $reviewable->isNotEmpty() && $hasUnreviewed ? $secondaryButton : $primaryButton }}">Beli Lagi</button>
                                </form>
                            @endif
                        </div>
                    </footer>

                    @if ($reviewable->isNotEmpty())
                        @include('orders.partials.review-dialog', ['order' => $order, 'reviews' => $reviews])
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ink-50 text-ink-400">
                        <svg class="h-8 w-8" aria-hidden="true"><use href="#i-receipt"/></svg>
                    </span>
                    @if ($status || filled($search))
                        <h2 class="mt-4 text-lg font-bold text-ink-900">Pesanan tidak ditemukan</h2>
                        <p class="mt-1 text-sm text-ink-500">Coba kata kunci atau tab status lain.</p>
                        <a href="{{ route('orders.index') }}" class="mt-5 inline-block rounded-full border border-ink-200 px-6 py-3 text-sm font-medium text-ink-800 transition hover:border-ink-900">Lihat semua pesanan</a>
                    @else
                        <h2 class="mt-4 text-lg font-bold text-ink-900">Belum ada pesanan</h2>
                        <p class="mt-1 text-sm text-ink-500">Pesanan yang selesai di-checkout akan tampil di halaman ini.</p>
                        <a href="{{ route('home') }}" class="mt-5 inline-block rounded-full bg-ink-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-ink-700">Mulai belanja</a>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
