@extends('layouts.app')

@section('title', 'Ubah Produk — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <div class="editor-layout">
        <header class="page-heading"><span class="section-code">ADMIN / PRODUK / UBAH</span><h1>{{ $product->name }}</h1></header>
        <form class="editor-form wide-form" method="POST" action="{{ route('admin.products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('admin.products._form', ['submitLabel' => 'Simpan perubahan'])
        </form>
    </div>
</div>
@endsection
