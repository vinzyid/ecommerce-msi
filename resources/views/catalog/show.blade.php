@extends('layouts.app')

@section('title', $product->name.' — '.config('app.name'))

@section('content')
@php
    $rating = $product->averageRating();
    $reviewCount = $product->reviews->count();
    $infoRows = collect([
        'Kategori' => $product->category->name,
        'SKU' => $product->sku,
        'Berat' => $product->weight_grams ? $product->weight_grams.' gram' : null,
        'Material' => $product->material,
        'Warna' => $product->color,
        'Dimensi' => $product->dimensions,
    ])->filter();
    $sold = max(3, ($product->id * 17) % 240);
@endphp

<div class="container-page pt-5">
    <nav class="flex flex-wrap items-center gap-2 text-sm text-ink-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
        <span class="text-ink-300">/</span>
        <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="hover:text-brand-700">{{ $product->category->name }}</a>
        <span class="text-ink-300">/</span>
        <b class="font-semibold text-ink-700">{{ $product->name }}</b>
    </nav>
</div>

<section class="container-page mt-5">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        {{-- Galeri --}}
        <div class="space-y-3">
            <div class="relative overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                @else
                    <div class="flex aspect-square w-full flex-col items-center justify-center bg-ink-900 text-white">
                        <span class="text-5xl font-black text-white/80">{{ strtoupper(substr($product->category->name, 0, 2)) }}</span>
                        <span class="mt-2 text-xs text-white/50">{{ $product->sku }}</span>
                    </div>
                @endif

                @if ($product->hasDiscount())
                    <span class="absolute left-4 top-4 rounded-lg bg-danger-500 px-3 py-1.5 text-sm font-bold text-white shadow">
                        -{{ $product->discountPercent() }}%
                    </span>
                @endif
                @if ($product->badge)
                    <span class="absolute right-4 top-4 rounded-lg bg-ink-900/85 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur">{{ $product->badge }}</span>
                @endif
            </div>

            @if ($product->image_url)
                <div class="grid grid-cols-4 gap-3">
                    @foreach (range(1, 4) as $i)
                        <button type="button"
                                class="overflow-hidden rounded-xl border-2 bg-white transition {{ $i === 1 ? 'border-brand-500' : 'border-ink-100 hover:border-brand-300' }}">
                            <img src="{{ $product->image_url }}" alt="Pratinjau {{ $i }}" class="aspect-square w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="lg:py-1">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <a href="{{ route('home', ['category' => $product->category->slug]) }}"
                   class="rounded-md bg-brand-50 px-2.5 py-1 text-brand-700 hover:bg-brand-100">{{ $product->category->name }}</a>
                <span class="text-ink-400">SKU {{ $product->sku }}</span>
            </div>

            <h1 class="mt-3 text-2xl font-extrabold leading-snug tracking-tight text-ink-900 sm:text-3xl">{{ $product->name }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                @if ($reviewCount > 0)
                    <span class="inline-flex items-center gap-1.5">
                        @include('components.stars', ['rating' => $rating, 'size' => 'md'])
                        <b class="font-bold text-ink-900">{{ number_format($rating, 1, ',', '.') }}</b>
                        <a href="#ulasan" class="text-ink-500 hover:text-brand-700">({{ $reviewCount }} ulasan)</a>
                    </span>
                @else
                    <span class="text-ink-500">Belum ada ulasan</span>
                @endif
                <span class="text-ink-300">|</span>
                <span class="text-ink-500"><b class="font-semibold text-ink-700">{{ $sold }}</b> terjual</span>
                <span class="text-ink-300">|</span>
                @if ($product->stock > 0)
                    <span class="font-semibold text-success-600">Stok {{ $product->stock }}</span>
                @else
                    <span class="font-semibold text-danger-600">Stok habis</span>
                @endif
            </div>

            <div class="mt-5 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <div class="flex flex-wrap items-end gap-3">
                    <span class="text-3xl font-extrabold tracking-tight text-ink-900">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                    @if ($product->hasDiscount())
                        <del class="text-base text-ink-400">Rp{{ number_format($product->compare_at_price, 0, ',', '.') }}</del>
                        <span class="rounded-md bg-danger-500/10 px-2 py-1 text-xs font-bold text-danger-600">
                            Hemat Rp{{ number_format($product->compare_at_price - $product->price, 0, ',', '.') }}
                        </span>
                    @endif
                </div>

                @if ($product->stock > 0)
                    <p class="mt-4 text-sm text-ink-500">
                        Belanja min. <b class="font-semibold text-ink-700">Rp300.000</b> untuk gratis ongkir.
                    </p>

                    @auth
                        <form method="POST" action="{{ route('cart.store') }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-sm font-semibold text-ink-700">Jumlah</span>
                                <div class="inline-flex items-center rounded-xl border border-ink-200 bg-white" data-quantity>
                                    <button type="button" data-quantity-step="-1"
                                            class="flex h-10 w-10 items-center justify-center text-ink-500 transition hover:text-brand-700" aria-label="Kurangi jumlah">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><use href="#i-minus"/></svg>
                                    </button>
                                    <input id="quantity" name="quantity" type="number" value="1" min="1" max="{{ max(1, $product->stock) }}"
                                           data-quantity-input
                                           class="h-10 w-14 border-x border-ink-200 text-center text-sm font-bold text-ink-900 outline-none [-moz-appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                    <button type="button" data-quantity-step="1"
                                            class="flex h-10 w-10 items-center justify-center text-ink-500 transition hover:text-brand-700" aria-label="Tambah jumlah">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><use href="#i-plus"/></svg>
                                    </button>
                                </div>
                                <span class="text-sm text-ink-500">Sisa {{ $product->stock }}</span>
                            </div>

                            <div class="flex flex-col gap-2.5 sm:flex-row">
                                <button type="submit"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border-2 border-brand-600 bg-white px-5 py-3 text-sm font-bold text-brand-700 transition hover:bg-brand-50">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cart"/></svg>
                                    + Keranjang
                                </button>
                                <button type="submit" name="buy_now" value="1"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-bolt"/></svg>
                                    Beli Sekarang
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" class="mt-2.5">
                            @csrf
                            @if ($isWishlisted)@method('DELETE')@endif
                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-ink-200 px-5 py-2.5 text-sm font-semibold transition {{ $isWishlisted ? 'border-danger-200 bg-danger-500/5 text-danger-600' : 'text-ink-600 hover:border-ink-300' }}">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <use href="{{ $isWishlisted ? '#i-heart-filled' : '#i-heart' }}"/>
                                </svg>
                                {{ $isWishlisted ? 'Tersimpan di wishlist' : 'Simpan ke wishlist' }}
                            </button>
                        </form>

                        @error('quantity')<p class="mt-2 text-sm font-semibold text-danger-600">{{ $message }}</p>@enderror
                    @else
                        <div class="mt-4 flex flex-col gap-2.5 sm:flex-row">
                            <a href="{{ route('login') }}"
                               class="inline-flex flex-1 items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700">
                                Masuk untuk membeli
                            </a>
                            <a href="{{ route('register') }}"
                               class="inline-flex flex-1 items-center justify-center rounded-xl border-2 border-brand-600 px-5 py-3 text-sm font-bold text-brand-700 transition hover:bg-brand-50">
                                Daftar akun
                            </a>
                        </div>
                    @endauth
                @else
                    <div class="mt-4 rounded-xl bg-ink-50 px-4 py-3 text-sm font-semibold text-ink-500">
                        Produk ini sedang kosong. Cek produk serupa di bawah.
                    </div>
                @endif

                <div class="mt-5 grid gap-3 border-t border-ink-100 pt-4 sm:grid-cols-3">
                    @foreach ([['i-truck', 'Gratis ongkir'], ['i-shield', 'Garansi resmi'], ['i-refresh', 'Retur 7 hari']] as [$icon, $label])
                        <div class="flex items-center gap-2 text-xs font-medium text-ink-600">
                            <svg class="h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Deskripsi & spesifikasi --}}
<section class="container-page mt-9">
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
            <h2 class="text-lg font-extrabold tracking-tight text-ink-900">Deskripsi Produk</h2>
            <p class="mt-3 text-sm leading-relaxed text-ink-600">{{ $product->description }}</p>
        </div>

        @if ($infoRows->isNotEmpty())
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <h2 class="text-lg font-extrabold tracking-tight text-ink-900">Spesifikasi</h2>
                <dl class="mt-3 divide-y divide-ink-100">
                    @foreach ($infoRows as $label => $value)
                        <div class="flex gap-4 py-2.5 text-sm">
                            <dt class="w-28 shrink-0 text-ink-500">{{ $label }}</dt>
                            <dd class="font-semibold text-ink-800">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</section>

{{-- Ulasan --}}
<section class="container-page mt-9" id="ulasan">
    <h2 class="text-xl font-extrabold tracking-tight text-ink-900">Ulasan Pembeli</h2>

    <div class="mt-4 grid gap-5 lg:grid-cols-[280px_1fr]">
        <aside class="h-fit rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
            @if ($reviewCount > 0)
                <div class="text-center">
                    <div class="text-4xl font-extrabold text-ink-900">{{ number_format($rating, 1, ',', '.') }}</div>
                    <div class="mt-1.5 flex justify-center">@include('components.stars', ['rating' => $rating, 'size' => 'md'])</div>
                    <p class="mt-1 text-xs text-ink-500">dari {{ $reviewCount }} ulasan</p>
                </div>
                <ul class="mt-5 space-y-2">
                    @foreach ($ratingBreakdown as $star => $count)
                        <li class="flex items-center gap-2.5 text-xs">
                            <span class="flex w-8 items-center gap-0.5 font-semibold text-ink-600">
                                {{ $star }}<svg class="h-3 w-3 text-accent-500" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-star-filled"/></svg>
                            </span>
                            <span class="h-2 flex-1 overflow-hidden rounded-full bg-ink-100">
                                <span class="block h-full rounded-full bg-accent-500" style="width: {{ $reviewCount > 0 ? round($count / $reviewCount * 100) : 0 }}%"></span>
                            </span>
                            <b class="w-5 text-right font-semibold text-ink-600">{{ $count }}</b>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="py-6 text-center text-sm text-ink-500">Produk ini belum memiliki ulasan.</p>
            @endif
        </aside>

        <div class="space-y-4">
            @auth
                @php $editing = $userReview !== null; @endphp
                <form method="POST" action="{{ route('reviews.store', $product) }}"
                      class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                    @csrf
                    <h3 class="text-base font-bold text-ink-900">{{ $editing ? 'Ubah ulasan Anda' : 'Tulis ulasan' }}</h3>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="rating" class="block text-sm font-semibold text-ink-700">Rating</label>
                            <select id="rating" name="rating"
                                    class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected((int) old('rating', $userReview?->rating) === $i)>{{ $i }} bintang</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="comment" class="block text-sm font-semibold text-ink-700">Komentar <span class="font-normal text-ink-400">(opsional)</span></label>
                            <textarea id="comment" name="comment" rows="3" maxlength="500"
                                      class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">{{ old('comment', $userReview?->comment) }}</textarea>
                        </div>

                        <button type="submit"
                                class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700">
                            {{ $editing ? 'Perbarui ulasan' : 'Kirim ulasan' }}
                        </button>
                    </div>
                </form>
            @else
                <p class="rounded-2xl border border-ink-100 bg-white p-5 text-sm text-ink-500 shadow-card">
                    <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Masuk</a> untuk menulis ulasan.
                </p>
            @endauth

            @forelse ($product->reviews as $review)
                <article class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ink-900 text-sm font-bold text-white">
                            {{ $review->user->initials() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <strong class="text-sm font-bold text-ink-900">{{ $review->user->username }}</strong>
                                @include('components.stars', ['rating' => $review->rating])
                            </div>
                            <time class="mt-0.5 block text-xs text-ink-400">{{ $review->created_at->translatedFormat('d M Y') }}</time>
                        </div>
                    </div>

                    @if ($review->comment)
                        <p class="mt-3 text-sm leading-relaxed text-ink-600">{{ $review->comment }}</p>
                    @endif

                    @auth
                        @if ($review->user_id === auth()->id() || auth()->user()->is_admin)
                            <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-danger-600 hover:text-danger-500">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                                    Hapus ulasan
                                </button>
                            </form>
                        @endif
                    @endauth
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-ink-200 bg-white p-8 text-center text-sm text-ink-500">
                    Jadilah yang pertama mengulas produk ini.
                </p>
            @endforelse
        </div>
    </div>
</section>

{{-- Produk terkait --}}
@if ($relatedProducts->isNotEmpty())
    <section class="container-page mt-12">
        <div class="mb-4">
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Produk serupa</span>
            <h2 class="mt-1 text-xl font-extrabold tracking-tight text-ink-900">Dalam kategori {{ $product->category->name }}</h2>
        </div>
        <div class="grid grid-cols-2 gap-3.5 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($relatedProducts as $related)
                <x-product-card :product="$related" :wishlisted-product-ids="$wishlistedProductIds" />
            @endforeach
        </div>
    </section>
@endif
@endsection
