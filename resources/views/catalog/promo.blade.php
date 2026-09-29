@extends('layouts.app')

@section('title', 'Promo Berjalan — '.config('app.name'))

@section('content')
<div class="page-shell">
    <header class="page-heading">
        <span class="section-code">KATALOG / PROMO</span>
        <h1>Promo berjalan</h1>
        <p>Produk dengan harga khusus selama masa promo.</p>
    </header>

    @php
        $vouchers = \App\Models\Voucher::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderBy('min_spend')
            ->get();
    @endphp

    @if ($vouchers->isNotEmpty())
        <section class="voucher-strip" aria-label="Kode promo">
            @foreach ($vouchers as $voucher)
                <article class="voucher-card">
                    <span class="voucher-code">{{ $voucher->code }}</span>
                    <strong>{{ $voucher->description }}</strong>
                    <small>
                        {{ $voucher->min_spend > 0 ? 'Minimal belanja Rp'.number_format($voucher->min_spend, 0, ',', '.') : 'Tanpa minimum belanja' }}
                    </small>
                </article>
            @endforeach
        </section>
    @endif

    <div class="product-grid">
        @forelse ($products as $product)
            @include('components.product-card', ['product' => $product])
        @empty
            <div class="empty-state">
                <h2>Belum ada produk promo</h2>
                <p>Nantikan penawaran berikutnya.</p>
                <a class="text-link" href="{{ route('home') }}">Lihat semua produk</a>
            </div>
        @endforelse
    </div>

    @if ($products->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($products->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $products->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</b>
            @if ($products->hasMorePages())<a href="{{ $products->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</div>
@endsection
