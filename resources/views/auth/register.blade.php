@extends('layouts.app')

@section('title', 'Daftar — '.config('app.name'))

@section('content')
@php
    $field = 'w-full rounded-xl border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-brand-500/20';
@endphp

<div class="container-page pt-10">
    <div class="mx-auto grid max-w-4xl overflow-hidden rounded-3xl border border-ink-100 bg-white shadow-lift lg:grid-cols-2">
        <section class="hidden flex-col justify-between bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-8 text-white lg:flex" aria-labelledby="register-heading">
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-brand-300">Akun / Daftar</span>
                <h1 id="register-heading" class="mt-3 text-2xl font-extrabold leading-tight tracking-tight">Buat akun untuk mulai belanja.</h1>
                <p class="mt-2 text-sm text-white/70">Setelah mendaftar, kamu langsung masuk ke halaman akun.</p>
            </div>

            <ul class="mt-8 space-y-3 text-sm text-white/85">
                @foreach ([
                    'Gratis dan mudah didaftarkan',
                    'Promo eksklusif untuk pelanggan',
                    'Simpan alamat pengiriman',
                ] as $benefit)
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-500/25 text-brand-200">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check"/></svg>
                        </span>
                        {{ $benefit }}
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="p-7 sm:p-9">
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Formulir Pendaftaran</span>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-ink-900">Data Akun</h2>
                <p class="mt-1 text-sm text-ink-500">Isi semua kolom. Password minimal 6 karakter.</p>
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" novalidate class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-semibold text-ink-700">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" maxlength="50" autofocus
                           class="{{ $field }} mt-1.5 {{ $errors->has('username') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-brand-500' }}">
                    @error('username')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-ink-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="100"
                           class="{{ $field }} mt-1.5 {{ $errors->has('email') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-brand-500' }}">
                    @error('email')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-ink-700">Password</label>
                        <div class="relative mt-1.5">
                            <input id="password" name="password" type="password" autocomplete="new-password" data-password-input
                                   class="{{ $field }} pr-11 {{ $errors->has('password') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-brand-500' }}">
                            <button type="button" data-password-toggle
                                    class="absolute right-2 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-ink-400 transition hover:bg-ink-100 hover:text-ink-700"
                                    aria-label="Tampilkan password" tabindex="-1">
                                <svg class="h-4.5 w-4.5" data-eye-open><use href="#i-eye"/></svg>
                                <svg class="hidden h-4.5 w-4.5" data-eye-closed><use href="#i-eye-off"/></svg>
                            </button>
                        </div>
                        @error('password')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-ink-700">Ulangi password</label>
                        <div class="relative mt-1.5">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" data-password-input
                                   class="{{ $field }} pr-11 border-ink-200 focus:border-brand-500">
                            <button type="button" data-password-toggle
                                    class="absolute right-2 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-ink-400 transition hover:bg-ink-100 hover:text-ink-700"
                                    aria-label="Tampilkan password" tabindex="-1">
                                <svg class="h-4.5 w-4.5" data-eye-open><use href="#i-eye"/></svg>
                                <svg class="hidden h-4.5 w-4.5" data-eye-closed><use href="#i-eye-off"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700">
                    Buat akun
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-ink-500">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Masuk</a>
            </p>
        </section>
    </div>
</div>
@endsection
