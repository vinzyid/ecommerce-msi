@extends('layouts.app')

@section('title', 'Lupa password — '.config('app.name'))

@section('content')
<div class="container-page py-14 sm:py-20">
    <div class="mx-auto max-w-sm">
        <div class="text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-ink-900">Lupa password</h1>
            <p class="mt-2 text-sm text-ink-500">Masukkan email akunmu, lalu buka link reset yang kami kirim.</p>
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-xl bg-danger-500/10 px-4 py-3 text-sm text-danger-600" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" novalidate class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm text-ink-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" autofocus class="w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none transition focus:ring-4 focus:ring-ink-100 mt-1.5">
            </div>
            <button type="submit" class="w-full rounded-full bg-ink-900 px-5 py-3 text-sm font-medium text-white hover:bg-ink-700">Kirim link reset</button>
        </form>

        <p class="mt-5 text-center text-sm text-ink-500">
            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Kembali ke halaman masuk</a>
        </p>
    </div>
</div>
@endsection
