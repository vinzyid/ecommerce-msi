@extends('layouts.app')

@section('title', 'Keranjang Belanja — NADI Market')

@section('content')
<div class="page-shell">
    <header class="page-heading heading-with-action">
        <div>
            <span class="section-code">BELANJA / KERANJANG</span>
            <h1>Keranjang belanja</h1>
            <p>Periksa kembali produk yang ingin Anda beli.</p>
        </div>
        @if ($cartItems->isNotEmpty())
            <form method="POST" action="{{ route('cart.destroyAll') }}">
                @csrf
                @method('DELETE')
                <button class="secondary-button" type="submit">Hapus semua</button>
            </form>
        @endif
    </header>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($cartItems->isEmpty())
        <div class="empty-state bordered">
            <h2>Keranjang masih kosong</h2>
            <p>Pilih produk dari katalog untuk mulai membuat pesanan.</p>
            <a class="primary-button button-link fit" href="{{ route('home') }}">Buka katalog</a>
        </div>
    @else
        <div class="cart-layout">
            <section class="cart-list" aria-label="Isi keranjang">
                @foreach ($cartItems as $item)
                    <article class="cart-row">
                        <a class="cart-thumb" href="{{ route('products.show', $item->product) }}" tabindex="-1" aria-hidden="true">
                            @if ($item->product->image_url)
                                <img src="{{ $item->product->image_url }}" alt="" loading="lazy">
                            @else
                                <span>{{ strtoupper(substr($item->product->category->name, 0, 2)) }}</span>
                            @endif
                        </a>
                        <div class="cart-product">
                            <span>{{ $item->product->sku }}</span>
                            <h2><a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a></h2>
                            <p>Rp{{ number_format($item->product->price, 0, ',', '.') }} / barang</p>
                            <small class="{{ $item->product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                                {{ $item->product->stock > 0 ? $item->product->stock.' stok tersedia' : 'Stok habis' }}
                            </small>
                        </div>
                        <form class="quantity-form" method="POST" action="{{ route('cart.update', $item) }}">
                            @csrf
                            @method('PATCH')
                            <label for="quantity-{{ $item->id }}">Jumlah</label>
                            <input id="quantity-{{ $item->id }}" name="quantity" type="number" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}">
                            <button type="submit">Ubah</button>
                        </form>
                        <div class="cart-subtotal">
                            <strong>Rp{{ number_format((int) $item->product->price * $item->quantity, 0, ',', '.') }}</strong>
                            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="remove-button" type="submit">
                                    <svg class="icon" aria-hidden="true"><use href="#i-trash"/></svg> Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="order-summary">
                <h2>Ringkasan belanja</h2>

                <dl>
                    <div><dt>Subtotal ({{ $cartItems->sum('quantity') }} produk)</dt><dd>Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                    @if ($discount > 0)
                        <div class="summary-discount"><dt>Diskon {{ $voucher?->code }}</dt><dd>-Rp{{ number_format($discount, 0, ',', '.') }}</dd></div>
                    @endif
                    <div><dt>Ongkos kirim</dt><dd>{{ $shipping === 0 ? 'Gratis' : 'Rp'.number_format($shipping, 0, ',', '.') }}</dd></div>
                </dl>

                <form class="voucher-form" method="POST" action="{{ $voucher ? route('voucher.remove') : route('voucher.apply') }}">
                    @csrf
                    @if ($voucher)
                        @method('DELETE')
                        <label>Kode promo terpasang</label>
                        <div>
                            <input value="{{ $voucher->code }}" readonly>
                            <button type="submit">Hapus</button>
                        </div>
                    @else
                        <label for="code">Kode promo</label>
                        <div>
                            <input id="code" name="code" placeholder="Contoh: HEMAT10" value="{{ old('code') }}">
                            <button type="submit">Pakai</button>
                        </div>
                    @endif
                </form>
                @error('code')<p class="field-error">{{ $message }}</p>@enderror

                <div class="summary-total"><span>Total</span><strong>Rp{{ number_format($total, 0, ',', '.') }}</strong></div>
                <a class="primary-button button-link" href="{{ route('checkout.create') }}">Lanjut ke checkout</a>
                <a class="text-link centered" href="{{ route('home') }}">&larr; Lanjut belanja</a>
            </aside>
        </div>

        <section class="trust-strip" aria-label="Informasi layanan">
            <div><svg class="icon" aria-hidden="true"><use href="#i-truck"/></svg><span><strong>Pengiriman Cepat</strong><small>Reguler dan kilat</small></span></div>
            <div><svg class="icon" aria-hidden="true"><use href="#i-lock"/></svg><span><strong>Pembayaran Aman</strong><small>COD dan transfer bank</small></span></div>
            <div><svg class="icon" aria-hidden="true"><use href="#i-refresh"/></svg><span><strong>Garansi Produk</strong><small>7 hari pengembalian</small></span></div>
            <div><svg class="icon" aria-hidden="true"><use href="#i-headset"/></svg><span><strong>Layanan Pelanggan</strong><small>Senin sampai Jumat, 08.00 sampai 22.00</small></span></div>
        </section>
    @endif
</div>
@endsection
