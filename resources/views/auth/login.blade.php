@extends('layouts.app')

@section('title', 'Masuk — '.config('app.name'))

@section('content')
@php
    $field = 'w-full rounded-xl border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-brand-500/20';
@endphp

<div class="container-page pt-10">
    <div class="mx-auto grid max-w-4xl overflow-hidden rounded-3xl border border-ink-100 bg-white shadow-lift lg:grid-cols-2">
        <section class="hidden flex-col justify-between bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 p-8 text-white lg:flex" aria-labelledby="login-heading">
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-brand-300">Akun / Masuk</span>
                <h1 id="login-heading" class="mt-3 text-2xl font-extrabold leading-tight tracking-tight">Masuk ke akun kamu.</h1>
                <p class="mt-2 text-sm text-white/70">Gunakan email atau username yang terdaftar.</p>
            </div>

            <ul class="mt-8 space-y-3 text-sm text-white/85">
                @foreach ([
                    'Belanja lebih cepat tanpa isi ulang data',
                    'Lacak pesanan dan status pengiriman',
                    'Simpan produk favorit di wishlist',
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
                <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Formulir Masuk</span>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-ink-900">Data Login</h2>
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-sm font-semibold text-danger-600" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="identity" class="block text-sm font-semibold text-ink-700">Email atau username</label>
                    <input id="identity" name="identity" type="text" value="{{ old('identity') }}" autocomplete="username" autofocus
                           class="{{ $field }} mt-1.5 {{ $errors->has('identity') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-brand-500' }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-ink-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password"
                           class="{{ $field }} mt-1.5 {{ $errors->has('password') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-brand-500' }}">
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700">
                    Masuk
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-ink-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Daftar</a>
            </p>
        </section>
    </div>
</div>
@endsection
