@extends('layouts.app')

@section('title', 'Ubah Kategori — Etalase')

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <div class="editor-layout">
        <header class="page-heading"><span class="section-code">ADMIN / KATEGORI / UBAH</span><h1>{{ $category->name }}</h1></header>
        <form class="editor-form" method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('admin.categories._form', ['submitLabel' => 'Simpan perubahan'])
        </form>
    </div>
</div>
@endsection
