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
        <input type="hidden" name="items" value="{{ $selectedItemIds ?? '' }}">
        <section class="space-y-6">
            {{-- Alamat --}}
            @php
                $recipient = old('customer_name', $defaultAddress?->recipient_name ?? auth()->user()->username);
                $phone = old('phone', $defaultAddress?->phone ?? '');
                $addressVal = old('address', $defaultAddress?->address ?? '');
                $province = old('province', $defaultAddress?->province ?? '');
                $city = old('city', $defaultAddress?->city ?? '');
                $district = old('district', $defaultAddress?->district ?? '');
                $postal = old('postal_code', $defaultAddress?->postal_code ?? '');
            @endphp
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card" data-address-box>
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">01</span>
                        <h2 class="text-base font-extrabold text-ink-900">Alamat Pengiriman</h2>
                    </div>
                    @if (isset($savedAddresses) && $savedAddresses->isNotEmpty())
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                            <svg class="h-3.5 w-3.5"><use href="#i-check-circle"/></svg>
                            Otomatis terisi dari alamat tersimpan
                        </span>
                    @endif
                </div>

                {{-- Kartu Pilihan Alamat Tersimpan (Shopee / Tokopedia Style) --}}
                @if (isset($savedAddresses) && $savedAddresses->isNotEmpty())
                    <div class="mb-5 space-y-2">
                        <span class="block text-xs font-bold uppercase tracking-wider text-ink-400">Pilih Alamat Tersimpan</span>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            @foreach ($savedAddresses as $addr)
                                <label class="relative flex cursor-pointer flex-col rounded-xl border-2 p-3.5 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/40 border-ink-100 hover:border-ink-200"
                                       data-address-option
                                       data-name="{{ $addr->recipient_name }}"
                                       data-phone="{{ $addr->phone }}"
                                       data-address="{{ $addr->address }}"
                                       data-province="{{ $addr->province }}"
                                       data-city="{{ $addr->city }}"
                                       data-district="{{ $addr->district }}"
                                       data-postal="{{ $addr->postal_code }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="_address_selector" value="{{ $addr->id }}"
                                                   @checked($defaultAddress?->id === $addr->id)
                                                   class="h-4 w-4 accent-brand-600">
                                            <span class="text-xs font-bold text-ink-900">{{ $addr->label }}</span>
                                        </div>
                                        @if ($addr->is_default)
                                            <span class="rounded bg-brand-600 px-1.5 py-0.5 text-[10px] font-bold text-white">Utama</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-xs font-bold text-ink-800">{{ $addr->recipient_name }} &bull; <span class="font-normal text-ink-500">{{ $addr->phone }}</span></p>
                                    <p class="mt-1 line-clamp-2 text-xs text-ink-500">{{ $addr->summary() }}</p>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="{{ $label }}">Nama lengkap</label>
                        <input id="customer_name" name="customer_name" value="{{ $recipient }}" class="{{ $field }} mt-1.5" required>
                        @error('customer_name')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="{{ $label }}">Nomor telepon</label>
                        <input id="phone" name="phone" value="{{ $phone }}" autocomplete="tel" class="{{ $field }} mt-1.5" required>
                        @error('phone')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="address" class="{{ $label }}">Alamat lengkap</label>
                    <textarea id="address" name="address" rows="3" autocomplete="street-address" class="{{ $field }} mt-1.5" required>{{ $addressVal }}</textarea>
                    @error('address')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="province" class="{{ $label }}">Provinsi</label>
                        <input id="province" name="province" value="{{ $province }}" class="{{ $field }} mt-1.5" required>
                        @error('province')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="city" class="{{ $label }}">Kota / Kabupaten</label>
                        <input id="city" name="city" value="{{ $city }}" class="{{ $field }} mt-1.5" required>
                        @error('city')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="district" class="{{ $label }}">Kecamatan</label>
                        <input id="district" name="district" value="{{ $district }}" class="{{ $field }} mt-1.5" required>
                        @error('district')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="postal_code" class="{{ $label }}">Kode pos</label>
                        <input id="postal_code" name="postal_code" value="{{ $postal }}" inputmode="numeric" class="{{ $field }} mt-1.5" required>
                        @error('postal_code')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Pengiriman --}}
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">02</span>
                        <h2 class="text-base font-extrabold text-ink-900">Metode Pengiriman</h2>
                    </div>
                    <span class="text-xs font-semibold text-emerald-600">Bebas Ongkir min. Rp300rb</span>
                </div>

                <div class="space-y-3">
                    <label class="flex cursor-pointer items-start gap-3.5 rounded-xl border-2 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 border-ink-100 hover:border-ink-200">
                        <input type="radio" name="shipping_method" value="regular" @checked(old('shipping_method', $shippingMethod ?? 'regular') === 'regular')
                               class="mt-1 h-4.5 w-4.5 accent-brand-600">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <b class="text-sm font-bold text-ink-900">Pengiriman Reguler (Standar)</b>
                                <span class="rounded bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">
                                    {{ $shippingCost === 0 ? 'Bebas Ongkir' : 'Rp15.000' }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">Estimasi tiba 2–4 hari kerja &bull; Kurir: JNE, SiCepat, atau J&amp;T Express.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3.5 rounded-xl border-2 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 border-ink-100 hover:border-ink-200">
                        <input type="radio" name="shipping_method" value="express" @checked(old('shipping_method', $shippingMethod ?? 'regular') === 'express')
                               class="mt-1 h-4.5 w-4.5 accent-brand-600">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <b class="text-sm font-bold text-ink-900">Pengiriman Kilat (Express)</b>
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700">Rp25.000</span>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">Estimasi tiba 1 hari kerja / Next Day &bull; Prioritas proses langsung.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Pembayaran --}}
            <div class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">03</span>
                        <h2 class="text-base font-extrabold text-ink-900">Metode Pembayaran</h2>
                    </div>
                    <span class="flex items-center gap-1 text-xs text-ink-500 font-medium">
                        <svg class="h-3.5 w-3.5 text-emerald-600"><use href="#i-shield"/></svg>
                        100% Aman
                    </span>
                </div>

                <div class="space-y-3">
                    {{-- Transfer Bank --}}
                    <label class="flex cursor-pointer items-start gap-3.5 rounded-xl border-2 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 border-ink-100 hover:border-ink-200">
                        <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')
                               class="mt-1 h-4.5 w-4.5 accent-brand-600">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <b class="text-sm font-bold text-ink-900">Transfer Bank &bull; Virtual Account</b>
                                <div class="flex items-center gap-1">
                                    <span class="rounded border border-ink-200 bg-ink-50 px-1.5 py-0.5 text-[10px] font-bold text-ink-700">BCA</span>
                                    <span class="rounded border border-ink-200 bg-ink-50 px-1.5 py-0.5 text-[10px] font-bold text-ink-700">Mandiri</span>
                                    <span class="rounded border border-ink-200 bg-ink-50 px-1.5 py-0.5 text-[10px] font-bold text-ink-700">BRI</span>
                                    <span class="rounded border border-ink-200 bg-ink-50 px-1.5 py-0.5 text-[10px] font-bold text-ink-700">BNI</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">Nomor rekening/VA dan petunjuk transfer akan ditampilkan setelah pesanan dibuat.</p>
                        </div>
                    </label>

                    {{-- COD --}}
                    <label class="flex cursor-pointer items-start gap-3.5 rounded-xl border-2 p-4 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 border-ink-100 hover:border-ink-200">
                        <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')
                               class="mt-1 h-4.5 w-4.5 accent-brand-600">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <b class="text-sm font-bold text-ink-900">Bayar di Tempat (COD)</b>
                                <span class="rounded bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">Tunai</span>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">Bayar langsung secara tunai kepada kurir saat barang sampai di alamatmu.</p>
                        </div>
                    </label>
                </div>

                <div class="mt-4">
                    <label for="notes" class="{{ $label }}">Catatan Pesanan <span class="font-normal text-ink-400">(opsional)</span></label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Contoh: Titipkan ke satpam jika sedang keluar..." class="{{ $field }} mt-1.5">{{ old('notes') }}</textarea>
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
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-brand-700 active:scale-[0.99] shadow-md">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
                    Bayar &amp; Buat Pesanan
                </button>
                <div class="mt-3.5 space-y-1.5 border-t border-ink-100 pt-3 text-center text-[11px] text-ink-400">
                    <p class="flex items-center justify-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-emerald-600"><use href="#i-lock"/></svg>
                        Pembayaran aman &bull; SSL 256-Bit Encrypted
                    </p>
                    <p class="flex items-center justify-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-brand-600"><use href="#i-shield"/></svg>
                        Jaminan garansi uang kembali 7 hari
                    </p>
                </div>
                <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-sm font-semibold text-ink-500 hover:text-brand-700">&larr; Kembali ke keranjang</a>
            </div>
        </aside>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('[data-address-option]');
        if (!options.length) return;

        const fields = {
            name: document.getElementById('customer_name'),
            phone: document.getElementById('phone'),
            address: document.getElementById('address'),
            province: document.getElementById('province'),
            city: document.getElementById('city'),
            district: document.getElementById('district'),
            postal: document.getElementById('postal_code'),
        };

        const applyAddress = (option) => {
            if (fields.name) fields.name.value = option.dataset.name || '';
            if (fields.phone) fields.phone.value = option.dataset.phone || '';
            if (fields.address) fields.address.value = option.dataset.address || '';
            if (fields.province) fields.province.value = option.dataset.province || '';
            if (fields.city) fields.city.value = option.dataset.city || '';
            if (fields.district) fields.district.value = option.dataset.district || '';
            if (fields.postal) fields.postal.value = option.dataset.postal || '';
        };

        options.forEach((option) => {
            const radio = option.querySelector('input[type="radio"]');
            if (!radio) return;

            radio.addEventListener('change', () => {
                if (radio.checked) applyAddress(option);
            });

            if (radio.checked) applyAddress(option);
        });
    });
</script>
@endsection
