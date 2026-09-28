@extends('layouts.app')

@section('title', 'Checkout — NADI Market')

@section('content')
<div class="page-shell">
    <header class="page-heading">
        <span class="section-code">BELANJA / CHECKOUT</span>
        <h1>Checkout</h1>
        <p>Lengkapi alamat, pilih pengiriman, lalu tentukan pembayaran.</p>
    </header>

    <ol class="checkout-steps" aria-label="Langkah checkout">
        <li class="is-active"><b>1</b><span>Alamat</span></li>
        <li><b>2</b><span>Pengiriman</span></li>
        <li><b>3</b><span>Pembayaran</span></li>
        <li><b>4</b><span>Konfirmasi</span></li>
    </ol>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="checkout-layout" method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <section class="checkout-form">
            <div class="section-heading"><h2>Alamat pengiriman</h2><span>01</span></div>
            <div class="field-row">
                <div class="field">
                    <label for="customer_name">Nama lengkap</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()->username) }}">
                    @error('customer_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="phone">Nomor telepon</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
                    @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="field">
                <label for="address">Alamat lengkap</label>
                <textarea id="address" name="address" rows="3" autocomplete="street-address">{{ old('address') }}</textarea>
                @error('address')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="province">Provinsi</label>
                    <input id="province" name="province" value="{{ old('province') }}">
                    @error('province')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="city">Kota / Kabupaten</label>
                    <input id="city" name="city" value="{{ old('city') }}">
                    @error('city')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="district">Kecamatan</label>
                    <input id="district" name="district" value="{{ old('district') }}">
                    @error('district')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="postal_code">Kode pos</label>
                    <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric">
                    @error('postal_code')<p class="field-error">{{ $message }}</p>@enderror

                </div>
            </div>

            <div class="section-heading payment-heading"><h2>Metode pengiriman</h2><span>02</span></div>
            <label class="radio-option">
                <input type="radio" name="shipping_method" value="regular" @checked(old('shipping_method', 'regular') === 'regular')>
                <span><b>Pengiriman Reguler</b><small>Estimasi 2 sampai 5 hari kerja. Gratis mulai Rp300.000.</small></span>
            </label>
            <label class="radio-option">
                <input type="radio" name="shipping_method" value="express" @checked(old('shipping_method') === 'express')>
                <span><b>Pengiriman Kilat</b><small>Estimasi 1 hari kerja dengan biaya Rp25.000.</small></span>
            </label>

            <div class="section-heading payment-heading"><h2>Metode pembayaran</h2><span>03</span></div>
            <label class="radio-option">
                <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')>
                <span><b>Bayar di tempat</b><small>Bayar kepada kurir saat pesanan tiba.</small></span>
            </label>
            <label class="radio-option">
                <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                <span><b>Transfer bank</b><small>Instruksi transfer dicatat setelah pesanan dibuat.</small></span>
            </label>

            <div class="field">
                <label for="notes">Catatan <small>opsional</small></label>
                <textarea id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                @error('notes')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </section>

        <aside class="order-summary checkout-summary">
            <h2>Ringkasan pesanan</h2>
            <ul class="summary-items">
                @foreach ($cartItems as $item)
                    <li>
                        <span>{{ $item->product->name }} &times; {{ $item->quantity }}</span>
                        <b>Rp{{ number_format((int) $item->product->price * $item->quantity, 0, ',', '.') }}</b>
                    </li>
                @endforeach
            </ul>
            <dl>
                <div><dt>Subtotal</dt><dd>Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                @if ($discount > 0)
                    <div class="summary-discount"><dt>Diskon {{ $voucher?->code }}</dt><dd>-Rp{{ number_format($discount, 0, ',', '.') }}</dd></div>
                @endif
                <div><dt>Ongkos kirim</dt><dd>{{ $shippingCost === 0 ? 'Gratis' : 'Rp'.number_format($shippingCost, 0, ',', '.') }}</dd></div>
            </dl>
            <div class="summary-total"><span>Total</span><strong>Rp{{ number_format($total, 0, ',', '.') }}</strong></div>
            <button class="primary-button" type="submit">Buat pesanan</button>
            <a class="text-link centered" href="{{ route('cart.index') }}">&larr; Kembali ke keranjang</a>
            <p class="form-note">Stok akan berkurang setelah Anda membuat pesanan.</p>
        </aside>
    </form>
</div>
@endsection
