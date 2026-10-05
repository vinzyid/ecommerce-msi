@extends('layouts.admin')

@section('title', 'Tambah Produk — '.config('app.name'))
@section('heading', 'Tambah Produk')

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('admin.products.store') }}"
          class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card sm:p-7">
        @csrf
        @include('admin.products._form', ['product' => null, 'submitLabel' => 'Simpan produk'])
    </form>
</div>
@endsection
