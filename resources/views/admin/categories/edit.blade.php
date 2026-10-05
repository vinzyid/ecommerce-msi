@extends('layouts.admin')

@section('title', 'Ubah Kategori — '.config('app.name'))
@section('heading', $category->name)

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}"
          class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card sm:p-7">
        @csrf
        @method('PUT')
        @include('admin.categories._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
</div>
@endsection
