@extends('layouts.app')

@section('title', 'Daftar — Etalase')
@section('body-class', 'auth-page')

@section('content')
<div class="auth-shell">
    <section class="auth-context" aria-labelledby="register-heading">
        <span class="section-code">AKUN / DAFTAR</span>
        <h1 id="register-heading">Buat akun untuk mulai belanja.</h1>
        <p>Setelah mendaftar, Anda akan langsung masuk ke halaman akun.</p>

        <div class="category-list" aria-label="Kategori toko">
            <span>Kebutuhan harian</span>
            <span>Rumah</span>
            <span>Kerja</span>
            <span>Hadiah</span>
        </div>
    </section>

    <section class="form-panel" aria-labelledby="form-heading">
        <div class="form-heading">
            <span>FORMULIR PENDAFTARAN</span>
            <h2 id="form-heading">Data akun</h2>
            <p>Isi semua kolom. Password minimal 6 karakter.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" maxlength="50" autofocus class="@error('username') is-invalid @enderror">
                @error('username')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="100" class="@error('email') is-invalid @enderror">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="@error('password') is-invalid @enderror">
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Ulangi password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                </div>
            </div>

            <button class="primary-button" type="submit">Buat akun</button>
        </form>

        <p class="form-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </section>
</div>
@endsection
