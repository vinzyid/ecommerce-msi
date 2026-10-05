<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Admin — '.config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.icons')
</head>
<body class="min-h-screen bg-[#f4f6fb] text-ink-900 antialiased">

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-ink-100 bg-white lg:flex">
        <div class="flex h-16 items-center gap-2 border-b border-ink-100 px-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-black text-white">N</span>
            <span class="text-base font-extrabold">NADI<span class="text-brand-600">PLAY</span></span>
        </div>
        @include('admin._nav')
        <div class="mt-auto border-t border-ink-100 p-3">
            <a href="{{ route('home') }}"
               class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold text-ink-600 transition hover:bg-ink-50">
                <svg class="h-4.5 w-4.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-store"/></svg>
                Lihat toko
            </a>
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-30 border-b border-ink-100 bg-white/95 backdrop-blur">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
                <button type="button" data-mobile-toggle
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-ink-600 hover:bg-ink-50 lg:hidden"
                        aria-label="Buka menu">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-menu"/></svg>
                </button>

                <div class="min-w-0">
                    <span class="text-[11px] font-bold uppercase tracking-wide text-brand-600">Panel Admin</span>
                    <h1 class="truncate text-base font-extrabold text-ink-900">@yield('heading', 'Ringkasan')</h1>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <a href="{{ route('home') }}"
                       class="hidden items-center gap-1.5 rounded-xl border border-ink-200 px-3.5 py-2 text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700 sm:flex">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-store"/></svg>
                        Toko
                    </a>

                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger aria-haspopup="true" aria-expanded="false"
                                class="flex items-center gap-2 rounded-xl px-2 py-1.5 hover:bg-ink-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ink-900 text-xs font-bold text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                            <svg class="h-4 w-4 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-chevron"/></svg>
                        </button>
                        <div data-dropdown-menu
                             class="invisible absolute right-0 top-full z-50 mt-2 w-52 origin-top-right scale-95 rounded-2xl border border-ink-100 bg-white p-1.5 opacity-0 shadow-lift transition duration-150">
                            <a href="{{ route('account') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-700 hover:bg-ink-50">
                                <svg class="h-4.5 w-4.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-user"/></svg> Akun saya
                            </a>
                            <div class="my-1.5 h-px bg-ink-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold text-danger-600 hover:bg-danger-500/10">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-logout"/></svg> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @if (session('success') || session('status'))
            <div class="border-b border-success-500/20 bg-success-500/10">
                <div class="flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-success-600 sm:px-6">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-check-circle"/></svg>
                    {{ session('success') ?? session('status') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="border-b border-danger-500/20 bg-danger-500/10">
                <div class="flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-danger-600 sm:px-6">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-info"/></svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>

{{-- Menu mobile admin --}}
<div data-mobile-menu class="fixed inset-0 z-50 hidden lg:hidden">
    <div data-mobile-backdrop class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"></div>
    <aside class="absolute left-0 top-0 flex h-full w-64 max-w-[80%] flex-col bg-white shadow-lift">
        <div class="flex h-16 items-center justify-between border-b border-ink-100 px-5">
            <span class="text-base font-extrabold">NADI<span class="text-brand-600">PLAY</span></span>
            <button type="button" data-mobile-close class="flex h-9 w-9 items-center justify-center rounded-xl text-ink-500 hover:bg-ink-50" aria-label="Tutup menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><use href="#i-close"/></svg>
            </button>
        </div>
        @include('admin._nav')
    </aside>
</div>

</body>
</html>
