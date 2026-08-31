@extends('layouts.app')

@section('title', 'Kategori Admin — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading heading-with-action">
        <div><span class="section-code">ADMIN / KATEGORI</span><h1>Kategori produk</h1></div>
        <a class="primary-button button-link fit" href="{{ route('admin.categories.create') }}">Tambah kategori</a>
    </header>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Nama</th><th>Slug</th><th>Produk</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong><small>{{ $category->description }}</small></td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>{{ $category->products_count }}</td>
                        <td>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td><a href="{{ route('admin.categories.edit', $category) }}">Ubah</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
