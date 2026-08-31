@extends('layouts.app')

@section('title', 'Tambah Kategori — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <div class="editor-layout">
        <header class="page-heading"><span class="section-code">ADMIN / KATEGORI / BARU</span><h1>Tambah kategori</h1></header>
        <form class="editor-form" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories._form', ['category' => null, 'submitLabel' => 'Simpan kategori'])
        </form>
    </div>
</div>
@endsection
