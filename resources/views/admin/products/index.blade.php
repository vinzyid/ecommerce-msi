@extends('layouts.admin')

@section('title', 'Produk Admin — '.config('app.name'))
@section('heading', 'Produk')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="relative min-w-0 flex-1 sm:max-w-xs">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="#i-search"/></svg>
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau SKU"
               class="w-full rounded-xl border border-ink-200 bg-white py-2.5 pl-10 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
    </form>

    <a href="{{ route('admin.products.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><use href="#i-plus"/></svg>
        Tambah produk
    </a>
</div>

@if ($errors->any())
    <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $errors->first() }}</div>
@endif

<section class="mt-5 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Produk</th>
                    <th class="px-5 py-3">Kategori</th>
                    <th class="px-5 py-3 text-right">Harga</th>
                    <th class="px-5 py-3 text-center">Stok</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($products as $product)
                    <tr class="transition hover:bg-ink-50/60">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="h-11 w-11 shrink-0 overflow-hidden rounded-xl border border-ink-100 bg-ink-50">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block max-w-xs truncate font-semibold text-ink-900">{{ $product->name }}</span>
                                    <code class="text-xs text-ink-400">{{ $product->sku }}</code>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-ink-600">{{ $product->category->name }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-ink-900">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span @class([
                                'inline-flex min-w-10 items-center justify-center rounded-lg px-2 py-1 text-xs font-bold',
                                'bg-danger-500/15 text-danger-600' => $product->stock <= 5,
                                'bg-ink-100 text-ink-600' => $product->stock > 5,
                            ])>{{ $product->stock }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($product->is_active)
                                <span class="inline-flex items-center rounded-lg bg-success-500/15 px-2.5 py-1 text-xs font-bold text-success-600">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-lg bg-ink-100 px-2.5 py-1 text-xs font-bold text-ink-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pencil"/></svg>
                                Ubah
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-ink-400">Produk tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($products->hasPages())
    <div class="mt-6">{{ $products->links() }}</div>
@endif
@endsection
