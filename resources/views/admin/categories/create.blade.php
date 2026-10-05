@extends('layouts.admin')

@section('title', 'Tambah Kategori — '.config('app.name'))
@section('heading', 'Tambah Kategori')

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.categories.store') }}"
          class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card sm:p-7">
        @csrf
        @include('admin.categories._form', ['category' => null, 'submitLabel' => 'Simpan kategori'])
    </form>
</div>
@endsection
