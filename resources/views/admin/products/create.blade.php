@extends('layouts.app')

@section('title', 'Tambah Produk — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <div class="editor-layout">
        <header class="page-heading"><span class="section-code">ADMIN / PRODUK / BARU</span><h1>Tambah produk</h1></header>
        <form class="editor-form wide-form" method="POST" action="{{ route('admin.products.store') }}">
            @csrf
            @include('admin.products._form', ['product' => null, 'submitLabel' => 'Simpan produk'])
        </form>
    </div>
</div>
@endsection
