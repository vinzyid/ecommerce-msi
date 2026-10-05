@extends('layouts.app')

@section('title', 'Checkout — '.config('app.name'))

@section('content')
@php
    $field = 'w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
    $label = 'block text-sm font-semibold text-ink-700';
@endphp

<div class="container-page pt-6">
    <header>
        <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Belanja / Checkout</span>
        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink-900 sm:text-3xl">Checkout</h1>
        <p class="mt-1 text-sm text-ink-500">Lengkapi alamat, pilih pengiriman, lalu tentukan pembayaran.</p>
    </header>

    {{-- Langkah --}}
    <ol class="mt-5 flex flex-wrap items-center gap-2 text-xs font-semibold sm:gap-3" aria-label="Langkah checkout">
        @foreach (['Alamat', 'Pengiriman', 'Pembayaran', 'Konfirmasi'] as $i => $step)
            <li class="flex items-center gap-2 {{ $i === 0 ? 'text-brand-700' : 'text-ink-400' }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-bold {{ $i === 0 ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-500' }}">{{ $i + 1 }}</span>
                <span>{{ $step }}</span>
            </li>
            @if ($i < 3)
                <span class="h-px w-5 bg-ink-200 sm:w-8"></span>
            @endif
        @endforeach
    </ol>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-5 lg:grid-cols-[1fr_360px]">
        @csrf
        <section class="space-y-6">
            {{-- Alamat --}}
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">01</span>
                    <h2 class="text-base font-extrabold text-ink-900">Alamat Pengiriman</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="{{ $label }}">Nama lengkap</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()->username) }}" class="{{ $field }} mt-1.5">
                        @error('customer_name')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="{{ $label }}">Nomor telepon</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="{{ $field }} mt-1.5">
                        @error('phone')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="address" class="{{ $label }}">Alamat lengkap</label>
                    <textarea id="address" name="address" rows="3" autocomplete="street-address" class="{{ $field }} mt-1.5">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="province" class="{{ $label }}">Provinsi</label>
                        <input id="province" name="province" value="{{ old('province') }}" class="{{ $field }} mt-1.5">
                        @error('province')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="city" class="{{ $label }}">Kota / Kabupaten</label>
                        <input id="city" name="city" value="{{ old('city') }}" class="{{ $field }} mt-1.5">
                        @error('city')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="district" class="{{ $label }}">Kecamatan</label>
                        <input id="district" name="district" value="{{ old('district') }}" class="{{ $field }} mt-1.5">
                        @error('district')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="postal_code" class="{{ $label }}">Kode pos</label>
                        <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" class="{{ $field }} mt-1.5">
                        @error('postal_code')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Pengiriman --}}
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">02</span>
                    <h2 class="text-base font-extrabold text-ink-900">Metode Pengiriman</h2>
                </div>

                <div class="space-y-3">
                    @foreach ([
                        ['regular', 'Pengiriman Reguler', 'Estimasi 2–5 hari kerja. Gratis mulai Rp300.000.'],
                        ['express', 'Pengiriman Kilat', 'Estimasi 1 hari kerja dengan biaya Rp25.000.'],
                    ] as [$value, $title, $desc])
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 border-ink-100 hover:border-ink-200">
                            <input type="radio" name="shipping_method" value="{{ $value }}" @checked(old('shipping_method', 'regular') === $value)
                                   class="mt-0.5 h-4.5 w-4.5 accent-brand-600">
                            <span class="min-w-0">
                                <b class="block text-sm font-bold text-ink-900">{{ $title }}</b>
                                <small class="mt-0.5 block text-xs text-ink-500">{{ $desc }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Pembayaran --}}
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">03</span>
                    <h2 class="text-base font-extrabold text-ink-900">Metode Pembayaran</h2>
                </div>

                <div class="space-y-3">
                    @foreach ([
                        ['cod', 'Bayar di tempat', 'Bayar kepada kurir saat pesanan tiba.'],
                        ['bank_transfer', 'Transfer bank', 'Instruksi transfer dicatat setelah pesanan dibuat.'],
                    ] as [$value, $title, $desc])
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 border-ink-100 hover:border-ink-200">
                            <input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', 'cod') === $value)
                                   class="mt-0.5 h-4.5 w-4.5 accent-brand-600">
                            <span class="min-w-0">
                                <b class="block text-sm font-bold text-ink-900">{{ $title }}</b>
                                <small class="mt-0.5 block text-xs text-ink-500">{{ $desc }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-4">
                    <label for="notes" class="{{ $label }}">Catatan <span class="font-normal text-ink-400">(opsional)</span></label>
                    <textarea id="notes" name="notes" rows="2" class="{{ $field }} mt-1.5">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- Ringkasan --}}
        <aside class="h-fit lg:sticky lg:top-32">
            <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <h2 class="text-base font-extrabold text-ink-900">Ringkasan Pesanan</h2>

                <ul class="mt-4 max-h-56 space-y-3 overflow-y-auto pr-1">
                    @foreach ($cartItems as $item)
                        <li class="flex items-start gap-3">
                            <span class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-ink-100 bg-ink-50">
                                @if ($item->product->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="line-clamp-2 block text-xs font-semibold text-ink-800">{{ $item->product->name }}</span>
                                <span class="text-xs text-ink-400">{{ $item->quantity }} &times; Rp{{ number_format($item->product->price, 0, ',', '.') }}</span>
                            </span>
                            <b class="shrink-0 text-xs font-bold text-ink-900">Rp{{ number_format((int) $item->product->price * $item->quantity, 0, ',', '.') }}</b>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2.5 border-t border-ink-100 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Subtotal</dt>
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
                        <dd class="font-semibold {{ $shippingCost === 0 ? 'text-success-600' : 'text-ink-800' }}">
                            {{ $shippingCost === 0 ? 'Gratis' : 'Rp'.number_format($shippingCost, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-end justify-between border-t border-ink-100 pt-4">
                    <span class="text-sm font-semibold text-ink-600">Total</span>
                    <strong class="text-xl font-extrabold text-ink-900">Rp{{ number_format($total, 0, ',', '.') }}</strong>
                </div>

                <button type="submit"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-brand-700">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
                    Buat pesanan
                </button>
                <a href="{{ route('cart.index') }}" class="mt-2.5 block text-center text-sm font-semibold text-ink-500 hover:text-brand-700">&larr; Kembali ke keranjang</a>
                <p class="mt-3 text-center text-xs text-ink-400">Stok akan berkurang setelah pesanan dibuat.</p>
            </div>
        </aside>
    </form>
</div>
@endsection
