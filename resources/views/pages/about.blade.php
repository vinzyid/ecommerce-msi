@extends('layouts.app')

@section('title', 'Tentang — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-3xl">
        <header class="overflow-hidden rounded-2xl bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-6 text-white sm:p-9">
            <span class="text-xs font-bold uppercase tracking-wide text-brand-300">Informasi / Tentang</span>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Tentang {{ config('app.name') }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-white/70">
                Toko online perlengkapan gaming, diecast, dan hobi koleksi untuk main, kerja, dan koleksi.
            </p>
        </header>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <article class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-bolt"/></svg>
                </span>
                <h2 class="mt-4 text-base font-bold text-ink-900">Tujuan kami</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-600">
                    {{ config('app.name') }} membantu gamer dan kolektor menemukan gear yang benar-benar dipakai. Setiap produk dipilih dengan mempertimbangkan performa, durabilitas, dan harga yang wajar.
                </p>
            </article>

            <article class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-shield"/></svg>
                </span>
                <h2 class="mt-4 text-base font-bold text-ink-900">Cara kerja</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-600">
                    Katalog menampilkan harga dan stok terkini. Saat checkout, sistem memeriksa ulang stok dan mengunci baris produk sebelum pesanan dibuat, sehingga jumlah yang tercatat selalu sesuai.
                </p>
            </article>

            <article class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-credit-card"/></svg>
                </span>
                <h2 class="mt-4 text-base font-bold text-ink-900">Pembayaran &amp; pengiriman</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink-600">
                    Tersedia pembayaran di tempat dan transfer bank. Pengiriman reguler gratis untuk pesanan mulai Rp300.000, serta opsi pengiriman kilat untuk kebutuhan mendesak.
                </p>
            </article>

            <article class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="#i-headset"/></svg>
                </span>
                <h2 class="mt-4 text-base font-bold text-ink-900">Hubungi kami</h2>
                <ul class="mt-2 space-y-1.5 text-sm text-ink-600">
                    <li>Telepon: <a href="tel:+6281234567890" class="font-semibold text-brand-600 hover:text-brand-700">+62 812-3456-7890</a></li>
                    <li>Email: <a href="mailto:halo@vinzyplay.test" class="font-semibold text-brand-600 hover:text-brand-700">halo@vinzyplay.test</a></li>
                    <li>Jam layanan: Senin–Jumat, 08.00–22.00</li>
                </ul>
            </article>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700">Mulai belanja</a>
            <a href="{{ route('promo') }}" class="rounded-xl border border-ink-200 bg-white px-6 py-3 text-sm font-bold text-ink-700 transition hover:border-ink-300">Lihat promo</a>
        </div>
    </div>
</div>
@endsection
