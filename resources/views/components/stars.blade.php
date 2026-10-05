@php
    $size = $size ?? 'sm';
    $dim = $size === 'lg' ? 'h-5 w-5' : ($size === 'md' ? 'h-4 w-4' : 'h-3.5 w-3.5');
@endphp
<span class="inline-flex items-center gap-0.5 text-accent-500" aria-label="Rating {{ number_format($rating, 1, ',', '.') }} dari 5">
    @for ($i = 1; $i <= 5; $i++)
        @if ($i <= round($rating))
            <svg class="{{ $dim }} shrink-0" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-star-filled"/></svg>
        @else
            <svg class="{{ $dim }} shrink-0 text-ink-300" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-star-filled"/></svg>
        @endif
    @endfor
</span>
