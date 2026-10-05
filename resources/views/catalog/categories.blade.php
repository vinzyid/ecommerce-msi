@extends('layouts.app')

@section('title', 'Kategori Produk — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <header class="rounded-2xl bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-6 text-white sm:p-8">
        <span class="text-xs font-bold uppercase tracking-wide text-brand-300">Katalog / Kategori</span>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Kategori Produk</h1>
        <p class="mt-2 max-w-xl text-sm text-white/70">Pilih kategori sesuai kebutuhanmu — dari gear gaming sampai koleksi diecast.</p>
    </header>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}"
               class="group flex items-start gap-4 rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 text-brand-600 transition group-hover:from-brand-500 group-hover:to-brand-700 group-hover:text-white">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-layers"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-ink-900 group-hover:text-brand-700">{{ $category->name }}</h2>
                    <p class="mt-1 line-clamp-2 text-sm text-ink-500">{{ $category->description }}</p>
                    <span class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-ink-100 px-2.5 py-1 text-xs font-semibold text-ink-600">
                        {{ $category->products_count }} produk
                    </span>
                </div>
                <svg class="mt-1 h-5 w-5 shrink-0 text-ink-300 transition group-hover:translate-x-1 group-hover:text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            </a>
        @endforeach
    </div>
</div>
@endsection
