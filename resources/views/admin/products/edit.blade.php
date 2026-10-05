@extends('layouts.admin')

@section('title', 'Ubah Produk — '.config('app.name'))
@section('heading', $product->name)

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('admin.products.update', $product) }}"
          class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card sm:p-7">
        @csrf
        @method('PUT')
        @include('admin.products._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
</div>
@endsection
