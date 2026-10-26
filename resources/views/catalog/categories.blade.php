@extends('layouts.app')

@section('title', 'Kategori Produk — '.config('app.name'))

@section('content')
<div class="container-page pt-12">
    <header class="max-w-2xl">
        <h1 class="text-4xl font-semibold tracking-tight text-ink-900 sm:text-5xl">Kategori</h1>
        <p class="mt-3 text-base text-ink-500 sm:text-lg">Pilih kategori sesuai kebutuhanmu, dari gear gaming sampai koleksi diecast.</p>
    </header>

    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}"
               class="group flex items-center gap-5 overflow-hidden rounded-[1.75rem] bg-ink-50 p-5 transition-colors hover:bg-ink-100">
                <span class="relative grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white text-ink-400">
                    <svg class="absolute h-8 w-8" aria-hidden="true"><use href="#{{ $category->icon() }}"/></svg>
                    @if ($cover = $categoryCovers[$category->id] ?? null)
                        <img src="{{ $cover }}" alt="" loading="lazy" onerror="this.remove()"
                             class="relative h-full w-full bg-white object-contain p-2.5 transition-[scale] duration-500 ease-(--ease-out) group-hover:scale-[1.06]">
                    @endif
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-lg font-semibold text-ink-900">{{ $category->name }}</span>
                    @if ($category->description)
                        <span class="mt-1 line-clamp-2 block text-sm text-ink-500">{{ $category->description }}</span>
                    @endif
                    <span class="mt-2 block text-xs font-medium text-brand-600">{{ $category->products_count }} produk &rsaquo;</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
@endsection
