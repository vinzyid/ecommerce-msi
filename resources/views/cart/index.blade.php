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
        {{-- Progress Bebas Ongkir (Tokopedia Style) --}}
        @php
            $freeShippingMin = \App\Support\CartCalculator::FREE_SHIPPING_MINIMUM;
            $remaining = max(0, $freeShippingMin - $subtotal);
            $progressPercent = min(100, (int) round($subtotal / $freeShippingMin * 100));
        @endphp
        <div class="mt-5 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
            @if ($remaining > 0)
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5"><use href="#i-truck"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-ink-800">
                            Tambah <b class="font-extrabold text-emerald-600">Rp{{ number_format($remaining, 0, ',', '.') }}</b> lagi untuk <b>Bebas Ongkir</b>!
                        </p>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-ink-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5"><use href="#i-check-circle"/></svg>
                    </span>
                    <p class="text-sm font-semibold text-emerald-700">
                        Selamat! Pesananmu sudah dapat <b>Bebas Ongkir</b> (hemat Rp{{ number_format(\App\Support\CartCalculator::SHIPPING_COST, 0, ',', '.') }}). 🎉
                    </p>
                </div>
            @endif
        </div>

        <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_360px]">
            {{-- Toko --}}
            <section class="space-y-3" aria-label="Isi keranjang">
                <div class="flex items-center justify-between gap-3 rounded-2xl border border-ink-100 bg-white px-4 py-3 shadow-card">
                    <div class="flex items-center gap-2.5">
                        <label class="flex cursor-pointer items-center">
                            <input type="checkbox" data-select-all checked
                                   class="h-4.5 w-4.5 rounded border-ink-300 accent-brand-600">
                        </label>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-600 to-brand-800 text-[11px] font-black text-white">VP</span>
                        <div class="leading-tight">
                            <div class="flex items-center gap-1.5">
                                <h2 class="text-sm font-bold text-ink-900">VinzyPlay Official Store</h2>
                                <svg class="h-3.5 w-3.5 text-purple-700"><use href="#i-badge-check"/></svg>
                            </div>
                            <p class="flex items-center gap-1 text-[11px] text-ink-500">
                                <svg class="h-3 w-3 text-brand-600"><use href="#i-map-pin"/></svg>
                                Dikirim dari Kota Yogyakarta
                            </p>
                        </div>
                    </div>
                    <span class="hidden rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 sm:inline-block">Bebas Ongkir</span>
                </div>

                @foreach ($cartItems as $item)
                    <article class="flex gap-3.5 rounded-2xl border border-ink-100 bg-white p-3.5 shadow-card sm:gap-4">
                        <label class="flex cursor-pointer items-start pt-0.5">
                            <input type="checkbox" checked
                                   data-cart-item
                                   data-item-id="{{ $item->id }}"
                                   data-price="{{ (int) $item->product->price }}"
                                   data-quantity="{{ $item->quantity }}"
                                   class="h-4.5 w-4.5 rounded border-ink-300 accent-brand-600">
                        </label>

                        <a href="{{ route('products.show', $item->product) }}" tabindex="-1" aria-hidden="true"
                           class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-ink-100 bg-ink-50 sm:h-24 sm:w-24">
                            @if ($item->product->image_url)
                                <img src="{{ $item->product->image_url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center bg-ink-900 text-sm font-black text-white/80">
                                    {{ strtoupper(substr($item->product->category->name, 0, 2)) }}
                                </span>
                            @endif
                        </a>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="line-clamp-2 text-sm font-semibold text-ink-900">
                                        <a href="{{ route('products.show', $item->product) }}" class="hover:text-brand-700">{{ $item->product->name }}</a>
                                    </h3>
                                    <p class="mt-0.5 text-[11px] text-ink-400">{{ $item->product->category->name }} &bull; {{ $item->product->sku }}</p>
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

                            <div class="mt-1.5">
                                <span class="text-base font-extrabold text-ink-900">Rp{{ number_format($item->product->price, 0, ',', '.') }}</span>
                                @if ($item->product->hasDiscount())
                                    <del class="ml-1.5 text-xs text-ink-400">Rp{{ number_format($item->product->compare_at_price, 0, ',', '.') }}</del>
                                @endif
                            </div>

                            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-2.5">
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="inline-flex items-center" data-quantity>
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $item->id }}" class="sr-only">Jumlah</label>
                                    <div class="inline-flex items-center overflow-hidden rounded-xl border border-ink-200">
                                        <button type="button" data-quantity-step="-1"
                                                class="flex h-8 w-8 items-center justify-center text-ink-500 transition hover:bg-ink-50 hover:text-brand-700"
                                                aria-label="Kurangi jumlah">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><use href="#i-minus"/></svg>
                                        </button>
                                        <input id="quantity-{{ $item->id }}" name="quantity" type="number" value="{{ $item->quantity }}"
                                               min="1" max="{{ max(1, $item->product->stock) }}" data-quantity-input
                                               class="h-8 w-11 border-x border-ink-200 text-center text-xs font-bold text-ink-900 outline-none [-moz-appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button" data-quantity-step="1"
                                                class="flex h-8 w-8 items-center justify-center text-ink-500 transition hover:bg-ink-50 hover:text-brand-700"
                                                aria-label="Tambah jumlah">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><use href="#i-plus"/></svg>
                                        </button>
                                    </div>
                                    <button type="submit" class="ml-2 rounded-lg border border-ink-200 px-2.5 py-1.5 text-[11px] font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">Ubah</button>
                                </form>

                                <div class="text-right">
                                    <div class="text-[10px] text-ink-400">Subtotal</div>
                                    <div class="text-sm font-extrabold text-ink-900">Rp{{ number_format((int) $item->product->price * $item->quantity, 0, ',', '.') }}</div>
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
                                <label for="code" class="block text-xs font-semibold text-ink-600">Punya kode promo / voucher?</label>
                                <div class="mt-1.5 flex gap-2">
                                    <input id="code" name="code" placeholder="Contoh: HEMAT10" value="{{ old('code') }}"
                                           class="min-w-0 flex-1 rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm font-mono font-bold uppercase outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    <button type="submit" class="rounded-xl bg-ink-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-800">Pakai</button>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px] text-ink-500">
                                    <span>Saran:</span>
                                    <button type="button" onclick="document.getElementById('code').value='HEMAT10'" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono font-bold text-brand-700 hover:bg-brand-100">HEMAT10</button>
                                    <button type="button" onclick="document.getElementById('code').value='ONGKIR15'" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono font-bold text-brand-700 hover:bg-brand-100">ONGKIR15</button>
                                    <button type="button" onclick="document.getElementById('code').value='GAMER25'" class="rounded bg-brand-50 px-1.5 py-0.5 font-mono font-bold text-brand-700 hover:bg-brand-100">GAMER25</button>
                                </div>
                            @endif
                        </form>
                        @error('code')<p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4 flex items-end justify-between border-t border-ink-100 pt-4">
                        <div>
                            <span class="text-sm font-semibold text-ink-600">Total</span>
                            <p class="text-[11px] text-ink-400" data-selected-count-label>{{ $cartItems->count() }} barang dipilih</p>
                        </div>
                        <strong class="text-xl font-extrabold text-ink-900" data-cart-total>Rp{{ number_format($total, 0, ',', '.') }}</strong>
                    </div>

                    <form id="checkout-form" method="GET" action="{{ route('checkout.create') }}">
                        <input type="hidden" name="items" id="selected-items-input" value="{{ $cartItems->pluck('id')->implode(',') }}">
                        <button type="submit" id="checkout-btn"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-brand-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                            Lanjut ke checkout (<span data-selected-count>{{ $cartItems->count() }}</span>)
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow-right"/></svg>
                        </button>
                    </form>

                    <p class="mt-3 flex items-center justify-center gap-1.5 text-center text-[11px] text-ink-400">
                        <svg class="h-3.5 w-3.5 text-emerald-600"><use href="#i-lock"/></svg>
                        Transaksi aman &amp; terenkripsi SSL
                    </p>
                    <a href="{{ route('home') }}" class="mt-2 block text-center text-sm font-semibold text-ink-500 hover:text-brand-700">&larr; Lanjut belanja</a>
                </div>
            </aside>
        </div>

        <section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Informasi layanan">
            @foreach ([
                ['i-truck', 'Pengiriman Cepat', 'Reguler dan kilat'],
                ['i-credit-card', 'Pembayaran Aman', 'QRIS, transfer bank, COD'],
                ['i-refresh', 'Garansi Produk', '7 hari pengembalian'],
                ['i-headset', 'Layanan Pelanggan', 'CS siaga 24 jam'],
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.querySelector('[data-select-all]');
        const itemCheckboxes = Array.from(document.querySelectorAll('[data-cart-item]'));
        const checkoutBtn = document.getElementById('checkout-btn');
        const selectedInput = document.getElementById('selected-items-input');
        const selectedCountEls = document.querySelectorAll('[data-selected-count]');
        const totalEl = document.querySelector('[data-cart-total]');
        const countLabel = document.querySelector('[data-selected-count-label]');

        if (!itemCheckboxes.length) return;

        const formatRupiah = (number) => 'Rp' + new Intl.NumberFormat('id-ID').format(number);

        const recalculate = () => {
            const checked = itemCheckboxes.filter((cb) => cb.checked);
            let subtotal = 0;
            const selectedIds = [];

            checked.forEach((cb) => {
                const price = parseInt(cb.dataset.price || '0', 10);
                const qty = parseInt(cb.dataset.quantity || '1', 10);
                subtotal += price * qty;
                selectedIds.push(cb.dataset.itemId);
            });

            // Update hidden input
            if (selectedInput) selectedInput.value = selectedIds.join(',');

            // Update UI
            if (checkoutBtn) checkoutBtn.disabled = checked.length === 0;
            selectedCountEls.forEach((el) => (el.textContent = checked.length));
            if (countLabel) countLabel.textContent = `${checked.length} barang dipilih`;
            if (totalEl) totalEl.textContent = formatRupiah(subtotal);

            // Update Select All state
            if (selectAll) {
                selectAll.checked = checked.length === itemCheckboxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < itemCheckboxes.length;
            }
        };

        selectAll?.addEventListener('change', () => {
            itemCheckboxes.forEach((cb) => (cb.checked = selectAll.checked));
            recalculate();
        });

        itemCheckboxes.forEach((cb) => {
            cb.addEventListener('change', recalculate);
        });

        recalculate();
    });
</script>
@endsection
