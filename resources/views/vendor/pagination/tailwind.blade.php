@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-center gap-1.5">
        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="flex h-9.5 w-9.5 items-center justify-center rounded-xl border border-ink-100 bg-white text-ink-300" aria-disabled="true" aria-label="Halaman sebelumnya">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="flex h-9.5 w-9.5 items-center justify-center rounded-xl border border-ink-200 bg-white text-ink-600 transition hover:border-brand-300 hover:text-brand-700" aria-label="Halaman sebelumnya">
                <svg class="h-4 w-4 rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron"/></svg>
            </a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="flex h-9.5 min-w-9.5 items-center justify-center rounded-xl px-2 text-sm font-semibold text-ink-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="flex h-9.5 min-w-9.5 items-center justify-center rounded-xl bg-brand-600 px-2.5 text-sm font-bold text-white shadow-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="flex h-9.5 min-w-9.5 items-center justify-center rounded-xl border border-ink-200 bg-white px-2.5 text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="flex h-9.5 w-9.5 items-center justify-center rounded-xl border border-ink-200 bg-white text-ink-600 transition hover:border-brand-300 hover:text-brand-700" aria-label="Halaman berikutnya">
                <svg class="h-4 w-4 -rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron"/></svg>
            </a>
        @else
            <span class="flex h-9.5 w-9.5 items-center justify-center rounded-xl border border-ink-100 bg-white text-ink-300" aria-disabled="true" aria-label="Halaman berikutnya">
                <svg class="h-4 w-4 -rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chevron"/></svg>
            </span>
        @endif
    </nav>
@endif
