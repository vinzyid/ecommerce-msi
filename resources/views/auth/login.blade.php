@extends('layouts.app')

@section('title', 'Masuk — Etalase')
@section('body-class', 'auth-page')

@section('content')
<div class="auth-shell">
    <section class="auth-context" aria-labelledby="login-heading">
        <span class="section-code">AKUN / MASUK</span>
        <h1 id="login-heading">Masuk ke akun Anda.</h1>
        <p>Gunakan email atau username yang terdaftar.</p>

        <div class="category-list" aria-label="Kategori toko">
            <span>Kebutuhan harian</span>
            <span>Rumah</span>
            <span>Kerja</span>
            <span>Hadiah</span>
        </div>
    </section>

    <section class="form-panel" aria-labelledby="form-heading">
        <div class="form-heading">
            <span>FORMULIR MASUK</span>
            <h2 id="form-heading">Data login</h2>
        </div>

        @if ($errors->any())
            <div class="alert" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="field">
                <label for="identity">Email atau username</label>
                <input id="identity" name="identity" type="text" value="{{ old('identity') }}" autocomplete="username" autofocus class="@error('identity') is-invalid @enderror">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" class="@error('password') is-invalid @enderror">
            </div>

            <button class="primary-button" type="submit">Masuk</button>
        </form>

        <p class="form-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </section>
</div>
@endsection
