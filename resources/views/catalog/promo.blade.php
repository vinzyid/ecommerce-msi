@extends('layouts.app')

@section('title', 'Promo Berjalan — '.config('app.name'))

@section('content')
@php
    $vouchers = \App\Models\Voucher::query()
        ->where('is_active', true)
        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
        ->orderBy('min_spend')
        ->get();
@endphp

<div class="container-page pt-6">
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-danger-600 via-danger-500 to-accent-600 p-6 text-white sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold ring-1 ring-white/25">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-flame"/></svg>
                Promo terbatas
            </span>
            <h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Promo Berjalan</h1>
            <p class="mt-2 max-w-xl text-sm text-white/85">Produk dengan harga khusus selama masa promo. Buruan sebelum kehabisan.</p>
        </div>
    </header>

    @if ($vouchers->isNotEmpty())
        <section class="mt-6" aria-label="Kode promo">
            <h2 class="mb-3 text-lg font-extrabold tracking-tight text-ink-900">Kode Voucher</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($vouchers as $voucher)
                    <article class="relative overflow-hidden rounded-2xl border border-dashed border-brand-300 bg-white p-5 shadow-card">
                        <div class="flex items-start justify-between gap-3">
                            <span class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-black tracking-wide text-white">{{ $voucher->code }}</span>
                            <svg class="h-5 w-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-ticket"/></svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-ink-800">{{ $voucher->description }}</p>
                        <p class="mt-1 text-xs text-ink-500">
                            {{ $voucher->min_spend > 0 ? 'Minimal belanja Rp'.number_format($voucher->min_spend, 0, ',', '.') : 'Tanpa minimum belanja' }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-8">
        <h2 class="mb-4 text-lg font-extrabold tracking-tight text-ink-900">Produk Diskon</h2>

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-tag"/></svg>
                <p class="mt-3 font-semibold text-ink-700">Belum ada produk promo</p>
                <p class="mt-1 text-sm text-ink-500">Nantikan penawaran berikutnya.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Lihat semua produk</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
                @endforeach
            </div>

            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </section>
</div>
@endsection
