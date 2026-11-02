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
    $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
    $inputClass = 'mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-ink-400 focus:ring-4 focus:ring-ink-100';
@endphp

<div class="container-page pt-6">
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-ink-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-ink-900">Beranda</a>
        <span aria-hidden="true">&rsaquo;</span>
        <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="hover:text-ink-900">{{ $product->category->name }}</a>
        <span aria-hidden="true">&rsaquo;</span>
        <span class="truncate text-ink-700">{{ $product->name }}</span>
    </nav>
</div>

<section class="container-page mt-6">
    <div class="grid gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-14">
        {{-- Foto --}}
        <div class="lg:sticky lg:top-24 lg:self-start">
            <div class="relative aspect-square overflow-hidden rounded-[2rem] bg-ink-50">
                <span class="absolute inset-0 grid place-items-center text-ink-300">
                    <svg class="h-16 w-16" aria-hidden="true"><use href="#{{ $product->category->icon() }}"/></svg>
                </span>
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" fetchpriority="high" onerror="this.remove()"
                         class="relative h-full w-full object-contain p-[8%] mix-blend-multiply">
                @endif
                @if ($product->hasDiscount())
                    <span class="absolute left-5 top-5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-danger-600 shadow-xs">
                        -{{ $product->discountPercent() }}%
                    </span>
                @endif
            </div>
        </div>

        {{-- Info & pembelian --}}
        <div>
            <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="text-sm font-medium text-brand-600 hover:underline">{{ $product->category->name }}</a>
            <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-ink-900 sm:text-4xl">{{ $product->name }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-500">
                @if ($reviewCount > 0)
                    <a href="#ulasan" class="inline-flex items-center gap-1.5 hover:text-ink-900">
                        @include('components.stars', ['rating' => $rating])
                        <span class="font-medium text-ink-900">{{ number_format($rating, 1, ',', '.') }}</span>
                        <span>({{ $reviewCount }} ulasan)</span>
                    </a>
                @else
                    <span>Belum ada ulasan</span>
                @endif
                <span aria-hidden="true">&middot;</span>
                <span>SKU {{ $product->sku }}</span>
            </div>

            @if ($product->badge)
                <p class="mt-4 inline-block rounded-full bg-ink-50 px-3 py-1 text-xs font-medium text-ink-700">{{ $product->badge }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1 tabular-nums">
                <span class="text-3xl font-semibold tracking-tight text-ink-900">{{ $rupiah($product->price) }}</span>
                @if ($product->hasDiscount())
                    <span class="text-base text-ink-400 line-through">{{ $rupiah($product->compare_at_price) }}</span>
                    <span class="text-sm font-medium text-danger-600">Hemat {{ $rupiah($product->compare_at_price - $product->price) }}</span>
                @endif
            </div>

            <p class="mt-4 text-sm">
                @if ($product->stock > 5)
                    <span class="font-medium text-success-600">Tersedia</span>
                @elseif ($product->stock > 0)
                    <span class="font-medium text-accent-600">Sisa {{ $product->stock }} unit</span>
                @else
                    <span class="font-medium text-danger-600">Stok habis</span>
                @endif
            </p>

            <div class="mt-6 border-t border-ink-100 pt-6">
                @if ($product->stock > 0)
                    @auth
                        <form method="POST" action="{{ route('cart.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="flex items-center gap-4">
                                <label for="quantity" class="text-sm text-ink-700">Jumlah</label>
                                <div class="inline-flex items-center rounded-full border border-ink-200" data-quantity>
                                    <button type="button" data-quantity-step="-1"
                                            class="grid h-10 w-10 place-items-center rounded-full text-ink-500 hover:text-ink-900" aria-label="Kurangi jumlah">
                                        <svg class="h-4 w-4" stroke-width="2" aria-hidden="true"><use href="#i-minus"/></svg>
                                    </button>
                                    <input id="quantity" name="quantity" type="number" value="1" min="1" max="{{ max(1, $product->stock) }}"
                                           data-quantity-input
                                           class="h-10 w-12 bg-transparent text-center text-sm font-medium tabular-nums text-ink-900 outline-none [-moz-appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                    <button type="button" data-quantity-step="1"
                                            class="grid h-10 w-10 place-items-center rounded-full text-ink-500 hover:text-ink-900" aria-label="Tambah jumlah">
                                        <svg class="h-4 w-4" stroke-width="2" aria-hidden="true"><use href="#i-plus"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <button type="submit" name="buy_now" value="1"
                                        class="rounded-full bg-ink-900 px-6 py-3.5 text-sm font-medium text-white hover:bg-ink-700">
                                    Beli sekarang
                                </button>
                                <button type="submit"
                                        class="rounded-full border border-ink-200 px-6 py-3.5 text-sm font-medium text-ink-900 hover:border-ink-900">
                                    Tambah ke keranjang
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" class="mt-3">
                            @csrf
                            @if ($isWishlisted)@method('DELETE')@endif
                            <button type="submit" class="inline-flex items-center gap-2 text-sm text-ink-700 hover:text-ink-900">
                                <svg class="h-4.5 w-4.5 {{ $isWishlisted ? 'text-danger-500' : '' }}" aria-hidden="true">
                                    <use href="{{ $isWishlisted ? '#i-heart-filled' : '#i-heart' }}"/>
                                </svg>
                                {{ $isWishlisted ? 'Tersimpan di wishlist' : 'Simpan ke wishlist' }}
                            </button>
                        </form>

                        @error('quantity')<p class="mt-2 text-sm font-medium text-danger-600">{{ $message }}</p>@enderror
                    @else
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <a href="{{ route('login') }}" class="rounded-full bg-ink-900 px-6 py-3.5 text-center text-sm font-medium text-white hover:bg-ink-700">Masuk untuk membeli</a>
                            <a href="{{ route('register') }}" class="rounded-full border border-ink-200 px-6 py-3.5 text-center text-sm font-medium text-ink-900 hover:border-ink-900">Daftar akun</a>
                        </div>
                    @endauth
                @else
                    <p class="rounded-2xl bg-ink-50 px-5 py-4 text-sm text-ink-600">Produk ini sedang kosong. Lihat produk serupa di bawah.</p>
                @endif
            </div>

            {{-- Layanan --}}
            <ul class="mt-8 divide-y divide-ink-100 rounded-2xl border border-ink-100 text-sm">
                @foreach ([
                    ['i-truck', 'Gratis ongkir', 'Untuk belanja min. Rp300.000 dengan Pengiriman Reguler via JNE.'],
                    ['i-shield', 'Original & bergaransi resmi', 'Dikirim langsung dari gudang kami di Yogyakarta.'],
                    ['i-refresh', 'Retur 7 hari', 'Barang rusak atau tidak sesuai, kami ganti baru.'],
                ] as [$icon, $title, $desc])
                    <li class="flex gap-3.5 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink-900" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
                        <div>
                            <p class="font-medium text-ink-900">{{ $title }}</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-ink-500">{{ $desc }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>

        </div>
    </div>
</section>

{{-- Kartu toko --}}
<section class="container-page mt-10">
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-[1.5rem] border border-ink-100 bg-white p-5">
        <div class="flex min-w-0 items-center gap-3">
            <x-logo :wordmark="false" class="[&>svg]:h-12 [&>svg]:w-12" />
            <div class="min-w-0">
                <p class="truncate font-semibold text-ink-900">{{ config('app.name') }}</p>
                <p class="text-xs text-ink-500">Toko gaming, diecast &amp; hobi</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="const toggle = document.querySelector('[data-chatbot-toggle]'); if (toggle?.getAttribute('aria-expanded') !== 'true') toggle?.click();"
                    class="inline-flex items-center gap-2 rounded-full bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-700">
                <svg class="h-4 w-4" aria-hidden="true"><use href="#i-headset"/></svg>
                Chat Toko
            </button>
            <a href="{{ route('home') }}" class="rounded-full border border-ink-200 px-4 py-2 text-sm font-medium text-ink-800 hover:border-ink-900">Kunjungi Toko</a>
        </div>
    </div>
</section>

{{-- Deskripsi & spesifikasi --}}
<section class="container-page mt-20">
    <div class="grid gap-12 border-t border-ink-100 pt-12 lg:grid-cols-2">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Tentang produk ini</h2>
            <p class="mt-4 text-base leading-relaxed text-ink-600">{{ $product->description }}</p>
        </div>

        @if ($infoRows->isNotEmpty())
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Spesifikasi</h2>
                <dl class="mt-4 divide-y divide-ink-100 border-y border-ink-100">
                    @foreach ($infoRows as $label => $value)
                        <div class="flex gap-4 py-3 text-sm">
                            <dt class="w-32 shrink-0 text-ink-500">{{ $label }}</dt>
                            <dd class="text-ink-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</section>

{{-- Ulasan --}}
<section class="container-page mt-20 scroll-mt-24" id="ulasan">
    <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Ulasan pembeli</h2>

    <div class="mt-8 grid gap-10 lg:grid-cols-[260px_1fr]">
        <aside class="h-fit">
            @if ($reviewCount > 0)
                <div class="text-5xl font-semibold tracking-tight text-ink-900">{{ number_format($rating, 1, ',', '.') }}</div>
                <div class="mt-2">@include('components.stars', ['rating' => $rating, 'size' => 'md'])</div>
                <p class="mt-1 text-sm text-ink-500">dari {{ $reviewCount }} ulasan</p>
                <ul class="mt-6 space-y-2">
                    @foreach ($ratingBreakdown as $star => $count)
                        <li class="flex items-center gap-3 text-xs text-ink-500">
                            <span class="w-3 tabular-nums">{{ $star }}</span>
                            <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink-100">
                                <span class="block h-full rounded-full bg-ink-900" style="width: {{ round($count / $reviewCount * 100) }}%"></span>
                            </span>
                            <span class="w-4 text-right tabular-nums">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-ink-500">Produk ini belum memiliki ulasan.</p>
            @endif
        </aside>

        <div class="space-y-4">
            @auth
                @if ($canReview)
                    @php $editing = $userReview !== null; @endphp
                    <form method="POST" action="{{ route('reviews.store', $product) }}" enctype="multipart/form-data" class="rounded-[1.5rem] bg-ink-50 p-6">
                        @csrf
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-base font-semibold text-ink-900">{{ $editing ? 'Ubah ulasan kamu' : 'Tulis ulasan' }}</h3>
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-success-600">
                                <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-check-circle"/></svg>
                                Pembeli terverifikasi
                            </span>
                        </div>

                        @error('review')
                            <p class="mt-3 rounded-xl bg-danger-500/10 px-3.5 py-2 text-xs font-medium text-danger-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="rating" class="block text-sm text-ink-700">Rating</label>
                                <select id="rating" name="rating" class="{{ $inputClass }}">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected((int) old('rating', $userReview?->rating) === $i)>{{ $i }} bintang {{ $i === 5 ? '(Sangat puas)' : ($i === 4 ? '(Puas)' : '') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="comment" class="block text-sm text-ink-700">Ulasan <span class="text-ink-400">(opsional)</span></label>
                                <textarea id="comment" name="comment" rows="3" maxlength="500" placeholder="Ceritakan kualitas produk, fungsi, dan pengemasan"
                                          class="{{ $inputClass }}">{{ old('comment', $userReview?->comment) }}</textarea>
                            </div>
                            <div>
                                <label for="photo" class="block text-sm text-ink-700">Foto <span class="text-ink-400">(opsional, maks. 2 MB)</span></label>
                                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                                       class="mt-1.5 block w-full text-xs text-ink-600 file:mr-3 file:rounded-full file:border file:border-ink-200 file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-ink-800">
                                @error('photo')<p class="mt-1.5 text-xs font-medium text-danger-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="rounded-full bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-700">
                                {{ $editing ? 'Perbarui ulasan' : 'Kirim ulasan' }}
                            </button>
                        </div>
                    </form>
                @else
                    <p class="rounded-[1.5rem] bg-ink-50 p-5 text-sm text-ink-600">
                        Ulasan hanya bisa ditulis oleh pembeli yang sudah menyelesaikan pesanan produk ini.
                    </p>
                @endif
            @else
                <p class="rounded-[1.5rem] bg-ink-50 p-5 text-sm text-ink-600">
                    <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Masuk</a> untuk menulis ulasan produk.
                </p>
            @endauth

            @forelse ($product->reviews as $review)
                <article class="border-b border-ink-100 py-5 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-ink-100 text-xs font-semibold text-ink-700">
                            {{ $review->user->initials() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                <strong class="text-sm font-medium text-ink-900">{{ $review->user->username }}</strong>
                                @include('components.stars', ['rating' => $review->rating])
                            </div>
                            <p class="text-xs text-ink-400">
                                <time>{{ $review->created_at->translatedFormat('d M Y') }}</time> &middot; Pembeli terverifikasi
                            </p>
                        </div>
                    </div>

                    @if ($review->comment)
                        <p class="mt-3 text-sm leading-relaxed text-ink-700">{{ $review->comment }}</p>
                    @endif

                    @if ($review->photo_path)
                        <a href="{{ $review->photo_path }}" target="_blank" rel="noopener" class="mt-3 inline-block">
                            <img src="{{ $review->photo_path }}" alt="Foto ulasan dari {{ $review->user->username }}" loading="lazy" class="h-24 w-24 rounded-xl object-cover">
                        </a>
                    @endif

                    @auth
                        @if ($review->user_id === auth()->id() || auth()->user()->is_admin)
                            <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-danger-600 hover:underline">Hapus ulasan</button>
                            </form>
                        @endif
                    @endauth
                </article>
            @empty
                <p class="py-8 text-sm text-ink-500">Jadilah yang pertama mengulas produk ini.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Produk terkait --}}
@if ($relatedProducts->isNotEmpty())
    <section class="container-page mt-20">
        <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Mungkin kamu juga suka</h2>
        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
            @foreach ($relatedProducts as $related)
                <x-product-card :product="$related" :wishlisted-product-ids="$wishlistedProductIds" />
            @endforeach
        </div>
    </section>
@endif

@if ($storeProducts->isNotEmpty())
    <section class="container-page mt-20">
        <h2 class="text-2xl font-semibold tracking-tight text-ink-900">Produk Lainnya dari Toko Ini</h2>
        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
            @foreach ($storeProducts as $storeProduct)
                <x-product-card :product="$storeProduct" :wishlisted-product-ids="$wishlistedProductIds" />
            @endforeach
        </div>
    </section>
@endif

{{-- Bar beli (ponsel), menutupi navigasi bawah agar tombol beli selalu terlihat --}}
<div class="fixed inset-x-0 bottom-0 z-[45] border-t border-ink-100 bg-white/90 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur-xl md:hidden">
    <div class="flex items-center gap-3">
        <div class="min-w-0 flex-1">
            <p class="truncate text-xs text-ink-500">{{ $product->name }}</p>
            <p class="text-base font-semibold tabular-nums text-ink-900">{{ $rupiah($product->price) }}</p>
        </div>
        @if ($product->stock > 0)
            @auth
                <form method="POST" action="{{ route('cart.store') }}" class="flex shrink-0 gap-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="grid h-11 w-11 place-items-center rounded-full border border-ink-200 text-ink-900" aria-label="Tambah ke keranjang">
                        <svg class="h-5 w-5" aria-hidden="true"><use href="#i-cart"/></svg>
                    </button>
                    <button type="submit" name="buy_now" value="1" class="rounded-full bg-ink-900 px-5 text-sm font-medium text-white">Beli</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="shrink-0 rounded-full bg-ink-900 px-5 py-3 text-sm font-medium text-white">Masuk untuk beli</a>
            @endauth
        @else
            <span class="shrink-0 rounded-full bg-ink-50 px-5 py-3 text-sm text-ink-500">Stok habis</span>
        @endif
    </div>
</div>
@endsection
