@extends('layouts.app')

@section('title', 'Produk Admin — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading heading-with-action">
        <div><span class="section-code">ADMIN / PRODUK</span><h1>Daftar produk</h1></div>
        <a class="primary-button button-link fit" href="{{ route('admin.products.create') }}">Tambah produk</a>
    </header>

    <form class="admin-search" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Cari nama atau SKU"><button type="submit">Cari</button></form>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Produk</th><th>SKU</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td><code>{{ $product->sku }}</code></td>
                        <td>{{ $product->category->name }}</td>
                        <td>Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                        <td @class(['stock-low' => $product->stock <= 5])>{{ $product->stock }}</td>
                        <td>{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td><a href="{{ route('admin.products.edit', $product) }}">Ubah</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">Produk tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
