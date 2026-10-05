@extends('layouts.admin')

@section('title', 'Kategori Admin — '.config('app.name'))
@section('heading', 'Kategori')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-ink-500">Kelola kategori yang tampil di katalog toko.</p>
    <a href="{{ route('admin.categories.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><use href="#i-plus"/></svg>
        Tambah kategori
    </a>
</div>

@if ($errors->any())
    <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600">{{ $errors->first() }}</div>
@endif

<section class="mt-5 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Nama</th>
                    <th class="px-5 py-3">Slug</th>
                    <th class="px-5 py-3 text-center">Produk</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($categories as $category)
                    <tr class="transition hover:bg-ink-50/60">
                        <td class="px-5 py-3.5">
                            <strong class="block font-semibold text-ink-900">{{ $category->name }}</strong>
                            <small class="text-xs text-ink-400">{{ $category->description }}</small>
                        </td>
                        <td class="px-5 py-3.5"><code class="rounded bg-ink-100 px-2 py-0.5 text-xs text-ink-600">{{ $category->slug }}</code></td>
                        <td class="px-5 py-3.5 text-center font-bold text-ink-700">{{ $category->products_count }}</td>
                        <td class="px-5 py-3.5">
                            @if ($category->is_active)
                                <span class="inline-flex items-center rounded-lg bg-success-500/15 px-2.5 py-1 text-xs font-bold text-success-600">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-lg bg-ink-100 px-2.5 py-1 text-xs font-bold text-ink-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-pencil"/></svg>
                                Ubah
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
