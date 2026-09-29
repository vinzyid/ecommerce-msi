@extends('layouts.app')

@section('title', 'Kategori Produk — '.config('app.name'))

@section('content')
<div class="page-shell">
    <header class="page-heading">
        <span class="section-code">KATALOG / KATEGORI</span>
        <h1>Kategori produk</h1>
        <p>Pilih kategori yang sesuai dengan kebutuhan Anda.</p>
    </header>

    <div class="category-cards">
        @foreach ($categories as $category)
            <a class="category-card" href="{{ route('home', ['category' => $category->slug]) }}">
                <span class="category-card-icon" aria-hidden="true"><svg class="icon"><use href="#i-layers"/></svg></span>
                <div>
                    <h2>{{ $category->name }}</h2>
                    <p>{{ $category->description }}</p>
                    <span class="category-card-count">{{ $category->products_count }} produk</span>
                </div>
                <svg class="icon category-card-arrow" aria-hidden="true"><use href="#i-arrow-right"/></svg>
            </a>
        @endforeach
    </div>
</div>
@endsection
