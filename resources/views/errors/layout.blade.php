<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Terjadi kendala — '.config('app.name'))</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.icons')
</head>
<body class="flex min-h-screen flex-col bg-white text-ink-900 antialiased">

<header class="border-b border-ink-100 bg-white">
    <div class="container-page flex h-16 items-center">
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('app.name') }}, halaman katalog">
            <x-logo />
        </a>
    </div>
</header>

<main class="flex flex-1 items-center justify-center px-4 py-14">
    <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-ink-100 bg-white shadow-lift">
        <div class="border-b border-ink-100 px-6 py-4 sm:px-8">
            <span class="text-xs font-medium uppercase tracking-widest text-ink-400">{{ strtoupper(config('app.name')) }} / ERROR</span>
        </div>

        <div class="p-7 sm:p-10">
            <h1 class="text-6xl font-semibold leading-none tracking-tighter text-ink-900 sm:text-7xl">
                @yield('code', 'Error')
            </h1>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-ink-900">@yield('message', 'Terjadi kendala')</h2>
            <p class="mt-3 text-sm leading-relaxed text-ink-500">
                @yield('description', 'Halaman tidak dapat dimuat. Silakan coba lagi atau kembali ke halaman utama.')
            </p>

            <div class="mt-7 flex flex-wrap items-center gap-3">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-ink-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-ink-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow-left"/></svg>
                    Kembali ke katalog
                </a>
                <a href="{{ route('categories.index') }}"
                   class="rounded-xl border border-ink-200 px-5 py-3 text-sm font-bold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                    Lihat kategori
                </a>
            </div>

            @hasSection('hint')
                <p class="mt-7 border-t border-ink-100 pt-5 text-xs uppercase tracking-wide text-ink-400">@yield('hint')</p>
            @endif
        </div>
    </div>
</main>

<footer class="border-t border-ink-100 bg-white">
    <div class="container-page flex flex-col gap-2 py-5 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
        <span class="uppercase tracking-wide">VINZYPLAY / GAMING, DIECAST &amp; HOBI</span>
        <span>Dikembangkan oleh Rafi Pandya P &copy; {{ now()->year }}</span>
    </div>
</footer>

</body>
</html>
