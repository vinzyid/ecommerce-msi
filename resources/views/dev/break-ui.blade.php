@extends('layouts.app')

@section('title', 'Break UI — Kartu Produk')

@section('content')
    <section class="container-page pt-6">
        <h1 class="text-xl font-extrabold text-ink-900">Uji kartu produk</h1>
        <p class="mt-1 text-sm text-ink-500">Halaman khusus dev. Ganti data lewat tombol di bawah layar.</p>

        <div class="mt-5 grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @forelse ($products as $product)
                <x-product-card :product="$product" :wishlisted-product-ids="$wishlistedProductIds" />
            @empty
                <p class="col-span-full text-sm text-ink-500">Tidak ada produk aktif di database.</p>
            @endforelse
        </div>
    </section>

    {{-- Toggle data (bukan bagian dari desain yang diuji) --}}
    <nav aria-label="Pilih data uji"
         style="position:fixed;left:50%;bottom:76px;transform:translateX(-50%);z-index:9998;display:flex;gap:2px;padding:3px;border-radius:999px;background:#e5e7eb;font:500 12px system-ui,sans-serif;box-shadow:0 1px 3px rgb(0 0 0 / .15)">
        @foreach ($states as $key => $label)
            <a href="{{ route('dev.break-ui', ['data' => $key]) }}"
               @if ($key === $state) aria-current="true" @endif
               style="padding:6px 12px;border-radius:999px;white-space:nowrap;color:{{ $key === $state ? '#111' : '#4b5563' }};background:{{ $key === $state ? '#fff' : 'transparent' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>
@endsection
