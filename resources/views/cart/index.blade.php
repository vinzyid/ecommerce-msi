@extends('layouts.app')

@section('title', 'Keranjang Belanja — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Belanja / Keranjang</span>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">Keranjang Belanja</h1>
            <p class="mt-1 text-sm text-ink-500">Periksa kembali produk yang ingin kamu beli.</p>
        </div>
        @if ($cartItems->isNotEmpty())
            <form method="POST" action="{{ route('cart.destroyAll') }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-ink-200 bg-white px-4 py-2.5 text-sm font-semibold text-danger-600 transition hover:border-danger-300 hover:bg-danger-500/5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                    Hapus semua
                </button>
            </form>
        @endif
    </header>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($cartItems->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-ink-200 bg-white py-16 text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-ink-50 text-ink-400">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><use href="#i-cart"/></svg>
            </span>
            <h2 class="mt-4 text-lg font-bold text-ink-900">Keranjang masih kosong</h2>
            <p class="mt-1 text-sm text-ink-500">Pilih produk dari katalog untuk mulai membuat pesanan.</p>
            <a href="{{ route('home') }}" class="mt-5 inline-block rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">Buka katalog</a>
        </div>
    @else
        <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_360px]">
            <section class="space-y-3" aria-label="Isi keranjang">
                @foreach ($cartItems as $item)
                    <article class="flex gap-4 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                        <a href="{{ route('products.show', $item->product) }}" tabindex="-1" aria-hidden="true"
                           class="h-24 w-24 shrink-0 overflow-hidden rounded-xl border border-ink-100 bg-ink-50 sm:h-28 sm:w-28">
                            @if ($item->product->image_url)
                                <img src="{{ $item->product->image_url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center bg-ink-900 text-sm font-black text-white/80">
                                    {{ strtoupper(substr($item->product->category->name, 0, 2)) }}
                                </span>
                            @endif
                        </a>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ $item->product->sku }}</span>
                                    <h2 class="mt-0.5 line-clamp-2 text-sm font-bold text-ink-900 sm:text-base">
                                        <a href="{{ route('products.show', $item->product) }}" class="hover:text-brand-700">{{ $item->product->name }}</a>
                                    </h2>
                                    <p class="mt-1 text-sm text-ink-500">Rp{{ number_format($item->product->price, 0, ',', '.') }} / barang</p>
                                </div>

                                <form method="POST" action="{{ route('cart.destroy', $item) }}" class="shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-400 transition hover:bg-danger-500/10 hover:text-danger-600"
                                            aria-label="Hapus {{ $item->product->name }}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-trash"/></svg>
                                    </button>
                                </form>
                            </div>

                            <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-3">
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $item->id }}" class="sr-only">Jumlah</label>
                                    <input id="quantity-{{ $item->id }}" name="quantity" type="number" value="{{ $item->quantity }}"
                                           min="1" max="{{ max(1, $item->product->stock) }}"
                                           class="h-9 w-16 rounded-xl border border-ink-200 text-center text-sm font-bold outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    <button type="submit" class="rounded-xl border border-ink-200 px-3 py-2 text-xs font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">Ubah</button>
                                </form>

                                <div class="text-right">
                                    <div class="text-xs text-ink-400">Subtotal</div>
                                    <div class="text-base font-extrabold text-ink-900">Rp{{ number_format((int) $item->product->price * $item->quantity, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            @if ($item->product->stock === 0)
                                <p class="mt-2 text-xs font-semibold text-danger-600">Stok habis — hapus untuk lanjut checkout.</p>
                            @elseif ($item->quantity > $item->product->stock)
                                <p class="mt-2 text-xs font-semibold text-danger-600">Jumlah melebihi stok ({{ $item->product->stock }} tersedia).</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="h-fit lg:sticky lg:top-32">
                <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                    <h2 class="text-base font-extrabold text-ink-900">Ringkasan Belanja</h2>

                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Subtotal ({{ $cartItems->sum('quantity') }} produk)</dt>
                            <dd class="font-semibold text-ink-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                        </div>
                        @if ($discount > 0)
                            <div class="flex justify-between text-success-600">
                                <dt class="font-semibold">Diskon {{ $voucher?->code }}</dt>
                                <dd class="font-bold">-Rp{{ number_format($discount, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Ongkos kirim</dt>
                            <dd class="font-semibold {{ $shipping === 0 ? 'text-success-600' : 'text-ink-800' }}">
                                {{ $shipping === 0 ? 'Gratis' : 'Rp'.number_format($shipping, 0, ',', '.') }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t border-ink-100 pt-4">
                        <form method="POST" action="{{ $voucher ? route('voucher.remove') : route('voucher.apply') }}">
                            @csrf
                            @if ($voucher)
                                @method('DELETE')
                                <label class="block text-xs font-semibold text-ink-600">Kode promo terpasang</label>
                                <div class="mt-1.5 flex gap-2">
                                    <input value="{{ $voucher->code }}" readonly
                                           class="min-w-0 flex-1 rounded-xl border border-success-500/30 bg-success-500/5 px-3.5 py-2.5 text-sm font-bold text-success-600">
                                    <button type="submit" class="rounded-xl border border-ink-200 px-4 py-2.5 text-sm font-semibold text-danger-600 transition hover:border-danger-300">Hapus</button>
                                </div>
                            @else
                                <label for="code" class="block text-xs font-semibold text-ink-600">Punya kode promo?</label>
                                <div class="mt-1.5 flex gap-2">
                                    <input id="code" name="code" placeholder="Contoh: GAMER10" value="{{ old('code') }}"
                                           class="min-w-0 flex-1 rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    <button type="submit" class="rounded-xl bg-ink-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-800">Pakai</button>
                                </div>
                            @endif
                        </form>
                        @error('code')<p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4 flex items-end justify-between border-t border-ink-100 pt-4">
                        <span class="text-sm font-semibold text-ink-600">Total</span>
                        <strong class="text-xl font-extrabold text-ink-900">Rp{{ number_format($total, 0, ',', '.') }}</strong>
                    </div>

                    <a href="{{ route('checkout.create') }}"
                       class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-brand-700">
                        Lanjut ke checkout
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow-right"/></svg>
                    </a>
                    <a href="{{ route('home') }}" class="mt-2.5 block text-center text-sm font-semibold text-ink-500 hover:text-brand-700">&larr; Lanjut belanja</a>
                </div>
            </aside>
        </div>

        <section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Informasi layanan">
            @foreach ([
                ['i-truck', 'Pengiriman Cepat', 'Reguler dan kilat'],
                ['i-lock', 'Pembayaran Aman', 'COD dan transfer bank'],
                ['i-refresh', 'Garansi Produk', '7 hari pengembalian'],
                ['i-headset', 'Layanan Pelanggan', 'Senin–Jumat, 08.00–22.00'],
            ] as [$icon, $title, $desc])
                <div class="flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#{{ $icon }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-ink-900">{{ $title }}</p>
                        <p class="truncate text-xs text-ink-500">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </section>
    @endif
</div>
@endsection
