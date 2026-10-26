@extends('layouts.app')

@section('title', 'Masuk — '.config('app.name'))

@section('content')
@php
    $field = 'w-full rounded-xl border px-3.5 py-2.5 text-sm outline-none transition focus:ring-4 focus:ring-ink-100';
@endphp

<div class="container-page py-14 sm:py-20">
    <div class="mx-auto max-w-sm">
        <section>
            <div class="text-center">
                <x-logo :wordmark="false" class="justify-center" />
                <h1 class="mt-6 text-3xl font-semibold tracking-tight text-ink-900">Masuk</h1>
                <p class="mt-2 text-sm text-ink-500">Gunakan email atau username yang terdaftar.</p>
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-xl bg-danger-500/10 px-4 py-3 text-sm text-danger-600" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="identity" class="block text-sm text-ink-700">Email atau username</label>
                    <input id="identity" name="identity" type="text" value="{{ old('identity') }}" autocomplete="username" autofocus
                           class="{{ $field }} mt-1.5 {{ $errors->has('identity') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-ink-400' }}">
                </div>

                <div>
                    <label for="password" class="block text-sm text-ink-700">Password</label>
                    <div class="relative mt-1.5">
                        <input id="password" name="password" type="password" autocomplete="current-password" data-password-input
                               class="{{ $field }} pr-11 {{ $errors->has('password') ? 'border-danger-400 focus:border-danger-500' : 'border-ink-200 focus:border-ink-400' }}">
                        <button type="button" data-password-toggle
                                class="absolute right-2 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg text-ink-400 transition hover:bg-ink-100 hover:text-ink-700"
                                aria-label="Tampilkan password" tabindex="-1">
                            <svg class="h-4.5 w-4.5" data-eye-open><use href="#i-eye"/></svg>
                            <svg class="hidden h-4.5 w-4.5" data-eye-closed><use href="#i-eye-off"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-full bg-ink-900 px-5 py-3 text-sm font-medium text-white hover:bg-ink-700">
                    Masuk
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-ink-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:underline">Daftar</a>
            </p>
        </section>
    </div>
</div>
@endsection
