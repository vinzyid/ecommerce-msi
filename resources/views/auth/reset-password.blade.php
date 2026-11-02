@extends('layouts.app')

@section('title', 'Buat password baru — '.config('app.name'))

@section('content')
<div class="container-page py-14 sm:py-20">
    <div class="mx-auto max-w-sm">
        <div class="text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-ink-900">Buat password baru</h1>
            <p class="mt-2 text-sm text-ink-500">Minimal 8 karakter, memuat huruf dan angka.</p>
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-xl bg-danger-500/10 px-4 py-3 text-sm text-danger-600" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" novalidate class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="block text-sm text-ink-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" class="w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none transition focus:ring-4 focus:ring-ink-100 mt-1.5">
            </div>
            <div>
                <label for="password" class="block text-sm text-ink-700">Password baru</label>
                <input id="password" name="password" type="password" autocomplete="new-password" autofocus class="w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none transition focus:ring-4 focus:ring-ink-100 mt-1.5">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm text-ink-700">Ulangi password baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none transition focus:ring-4 focus:ring-ink-100 mt-1.5">
            </div>
            <button type="submit" class="w-full rounded-full bg-ink-900 px-5 py-3 text-sm font-medium text-white hover:bg-ink-700">Simpan password</button>
        </form>
    </div>
</div>
@endsection
