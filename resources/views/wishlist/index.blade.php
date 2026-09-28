@extends('layouts.app')

@section('title', 'Wishlist — NADI Market')

@section('content')
<div class="page-shell">
    <header class="page-heading heading-with-action">
        <div>
            <span class="section-code">AKUN / WISHLIST</span>
            <h1>Wishlist</h1>
            <p>{{ $wishlists->total() }} produk tersimpan.</p>
        </div>
        @if ($wishlists->isNotEmpty())
            <form method="POST" action="{{ route('wishlist.destroyAll') }}">
                @csrf
                @method('DELETE')
                <button class="secondary-button" type="submit">Kosongkan wishlist</button>
            </form>
        @endif
    </header>

    @php
        $wishlistedProductIds = $wishlists->pluck('product_id')->all();
    @endphp

    <div class="product-grid">
        @forelse ($wishlists as $wishlist)
            @include('components.product-card', ['product' => $wishlist->product])
        @empty
            <div class="empty-state bordered">
                <h2>Wishlist masih kosong</h2>
                <p>Simpan produk favorit Anda dengan menekan ikon hati di kartu produk.</p>
                <a class="primary-button button-link fit" href="{{ route('home') }}">Buka katalog</a>
            </div>
        @endforelse
    </div>

    @if ($wishlists->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($wishlists->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $wishlists->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $wishlists->currentPage() }} dari {{ $wishlists->lastPage() }}</b>
            @if ($wishlists->hasMorePages())<a href="{{ $wishlists->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</div>
@endsection
