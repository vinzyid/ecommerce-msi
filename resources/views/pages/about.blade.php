@extends('layouts.app')

@section('title', 'Tentang — '.config('app.name'))

@section('content')
<div class="page-shell narrow-shell">
    <header class="page-heading">
        <span class="section-code">INFORMASI / TENTANG</span>
        <h1>Tentang {{ config('app.name') }}</h1>
        <p>Toko online perlengkapan rumah, meja kerja, dan kebutuhan harian.</p>
    </header>

    <div class="about-grid">
        <article class="about-card">
            <h2>Tujuan</h2>
            <p>{{ config('app.name') }} membantu pelanggan menemukan perlengkapan yang benar-benar dipakai setiap hari. Setiap produk dipilih dengan mempertimbangkan fungsi, ketahanan, dan harga yang wajar.</p>
        </article>

        <article class="about-card">
            <h2>Cara kerja</h2>
            <p>Katalog menampilkan harga dan stok terkini. Saat checkout, sistem memeriksa ulang stok dan mengunci baris produk sebelum pesanan dibuat, sehingga jumlah yang tercatat selalu sesuai.</p>
        </article>

        <article class="about-card">
            <h2>Pembayaran dan pengiriman</h2>
            <p>Tersedia pembayaran di tempat dan transfer bank. Pengiriman reguler gratis untuk pesanan mulai Rp300.000, serta opsi pengiriman kilat untuk kebutuhan mendesak.</p>
        </article>

        <article class="about-card">
            <h2>Bantuan</h2>
            <ul>
                <li>Telepon: <a href="tel:+6281234567890">+62 812-3456-7890</a></li>
                <li>Email: <a href="mailto:halo@nadimarket.test">halo@nadimarket.test</a></li>
                <li>Jam layanan: Senin sampai Jumat, 08.00 sampai 22.00</li>
            </ul>
        </article>
    </div>

    <div class="about-cta">
        <a class="primary-button button-link fit" href="{{ route('home') }}">Mulai belanja</a>
        <a class="secondary-button button-link fit" href="{{ route('promo') }}">Lihat promo</a>
    </div>
</div>
@endsection
