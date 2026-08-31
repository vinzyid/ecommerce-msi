@extends('layouts.app')

@section('title', 'Cart — Etalase')

@section('content')
<div class="page-shell">
    <header class="page-heading">
        <span class="section-code">BELANJA / CART</span>
        <h1>Cart Anda</h1>
        <p>{{ $cartItems->sum('quantity') }} barang dalam {{ $cartItems->count() }} jenis produk.</p>
    </header>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($cartItems->isEmpty())
        <div class="empty-state bordered">
            <h2>Cart masih kosong</h2>
            <p>Pilih produk dari katalog untuk mulai membuat pesanan.</p>
            <a class="primary-button button-link fit" href="{{ route('home') }}">Buka katalog</a>
        </div>
    @else
        <div class="cart-layout">
            <section class="cart-list" aria-label="Isi cart">
                @foreach ($cartItems as $item)
                    <article class="cart-row">
                        <div class="cart-code">{{ strtoupper(substr($item->product->category->name, 0, 2)) }}</div>
                        <div class="cart-product">
                            <span>{{ $item->product->sku }}</span>
                            <h2><a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a></h2>
                            <p>Rp{{ number_format($item->product->price, 0, ',', '.') }} / barang</p>
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
                                <button class="remove-button" type="submit">Hapus</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="order-summary">
                <h2>Ringkasan</h2>
                <dl>
                    <div><dt>Subtotal</dt><dd>Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                    <div><dt>Ongkir</dt><dd>Dihitung saat checkout</dd></div>
                </dl>
                <div class="summary-total"><span>Total sementara</span><strong>Rp{{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                <a class="primary-button button-link" href="{{ route('checkout.create') }}">Lanjut checkout</a>
                <a class="text-link centered" href="{{ route('home') }}">Lanjut belanja</a>
            </aside>
        </div>
    @endif
</div>
@endsection
