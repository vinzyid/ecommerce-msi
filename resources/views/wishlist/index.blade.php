@extends('layouts.app')

@section('title', 'Wishlist — '.config('app.name'))

@section('content')
@php
    $wishlistedProductIds = $wishlists->pluck('product_id')->all();
@endphp

<div class="container-page pt-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Akun / Wishlist</span>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">Wishlist</h1>
            <p class="mt-1 text-sm text-ink-500">{{ $wishlists->total() }} produk tersimpan.</p>
        </div>
        @if ($wishlists->isNotEmpty())
            <form method="POST" action="{{ route('wishlist.destroyAll') }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-ink-200 bg-white px-4 py-2.5 text-sm font-semibold text-danger-600 transition hover:border-danger-300 hover:bg-danger-500/5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                    Kosongkan wishlist
                </button>
            </form>
        @endif
    </header>

    @if ($wishlists->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ink-50 text-ink-400">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><use href="#i-heart"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-bold text-ink-900">Wishlist masih kosong</h2>
            <p class="mt-1 text-sm text-ink-500">Simpan produk favoritmu dengan menekan ikon hati di kartu produk.</p>
            <a href="{{ route('home') }}" class="mt-5 inline-block rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">Buka katalog</a>
        </div>
    @else
        <div class="mt-6 grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($wishlists as $wishlist)
                @if ($wishlist->product)
                    <x-product-card :product="$wishlist->product" :wishlisted-product-ids="$wishlistedProductIds" />
                @endif
            @endforeach
        </div>

        @if ($wishlists->hasPages())
            <div class="mt-8">{{ $wishlists->links() }}</div>
        @endif
    @endif
</div>
@endsection
