@extends('layouts.app')

@section('title', 'Promo Berjalan — '.config('app.name'))

@section('content')
@php
    $vouchers = \App\Models\Voucher::query()
        ->where('is_active', true)
        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
        ->orderBy('min_spend')
        ->get()
        ->filter(fn ($voucher) => $voucher->isUsable());
@endphp

<div class="container-page pt-12">
    <header class="max-w-2xl">
        <p class="text-sm font-medium text-danger-600">Promo terbatas</p>
        <h1 class="mt-2 text-4xl font-semibold tracking-tight text-ink-900 sm:text-5xl">Promo berjalan</h1>
        <p class="mt-3 text-base text-ink-500 sm:text-lg">Harga khusus selama masa promo, selama stok masih ada.</p>
    </header>

    @if ($vouchers->isNotEmpty())
        <section class="mt-12" aria-label="Kode voucher">
            <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Kode voucher</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($vouchers as $voucher)
                    <article class="flex items-center justify-between gap-4 rounded-2xl border border-ink-200 p-5">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-semibold tracking-wide text-ink-900">{{ $voucher->code }}</p>
                            <p class="mt-1 text-sm text-ink-700">{{ $voucher->description }}</p>
                            <p class="mt-0.5 text-xs text-ink-500">
                                {{ $voucher->min_spend > 0 ? 'Min. belanja Rp'.number_format($voucher->min_spend, 0, ',', '.') : 'Tanpa minimum belanja' }}
                            </p>
                        </div>
                        <button type="button" data-copy-voucher="{{ $voucher->code }}"
                                class="shrink-0 rounded-full bg-ink-50 px-3.5 py-2 text-xs font-medium text-ink-900 hover:bg-ink-100">Salin</button>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-16">
        <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Produk diskon</h2>

        @if ($products->isEmpty())
            <div class="mt-6 rounded-[1.75rem] bg-ink-50 px-6 py-20 text-center">
                <svg class="mx-auto h-10 w-10 text-ink-300" aria-hidden="true"><use href="#i-tag"/></svg>
                <p class="mt-4 text-lg font-medium text-ink-900">Belum ada produk promo</p>
                <p class="mt-1 text-sm text-ink-500">Nantikan penawaran berikutnya.</p>
                <a href="{{ route('home') }}" class="press mt-6 inline-block rounded-full bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-700">Lihat semua produk</a>
            </div>
        @else
            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
                @endforeach
            </div>

            <div class="mt-12">{{ $products->links() }}</div>
        @endif
    </section>
</div>
@endsection
