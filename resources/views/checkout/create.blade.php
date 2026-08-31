@extends('layouts.app')

@section('title', 'Checkout — Etalase')

@section('content')
<div class="page-shell">
    <header class="page-heading">
        <span class="section-code">BELANJA / CHECKOUT</span>
        <h1>Alamat dan pembayaran</h1>
    </header>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form class="checkout-layout" method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <section class="checkout-form">
            <div class="section-heading"><h2>Data penerima</h2><span>01</span></div>
            <div class="field-row">
                <div class="field">
                    <label for="customer_name">Nama penerima</label>
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
                <textarea id="address" name="address" rows="5" autocomplete="street-address">{{ old('address') }}</textarea>
                @error('address')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="notes">Catatan <small>opsional</small></label>
                <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                @error('notes')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="section-heading payment-heading"><h2>Metode pembayaran</h2><span>02</span></div>
            <label class="radio-option">
                <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')>
                <span><b>Bayar di tempat</b><small>Bayar kepada kurir saat pesanan tiba.</small></span>
            </label>
            <label class="radio-option">
                <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                <span><b>Transfer bank</b><small>Instruksi transfer dicatat setelah pesanan dibuat.</small></span>
            </label>
        </section>

        <aside class="order-summary checkout-summary">
            <h2>Pesanan</h2>
            <ul class="summary-items">
                @foreach ($cartItems as $item)
                    <li><span>{{ $item->product->name }} × {{ $item->quantity }}</span><b>Rp{{ number_format((int) $item->product->price * $item->quantity, 0, ',', '.') }}</b></li>
                @endforeach
            </ul>
            <dl>
                <div><dt>Subtotal</dt><dd>Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                <div><dt>Ongkir</dt><dd>{{ $shippingCost === 0 ? 'Gratis' : 'Rp'.number_format($shippingCost, 0, ',', '.') }}</dd></div>
            </dl>
            <div class="summary-total"><span>Total</span><strong>Rp{{ number_format($subtotal + $shippingCost, 0, ',', '.') }}</strong></div>
            <button class="primary-button" type="submit">Buat pesanan</button>
            <p class="form-note">Stok akan berkurang setelah Anda membuat pesanan.</p>
        </aside>
    </form>
</div>
@endsection
