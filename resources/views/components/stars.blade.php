@php
    $full = (int) floor($rating);
    $hasHalf = ($rating - $full) >= 0.5;
@endphp
<span class="stars" aria-label="Rating {{ number_format($rating, 1, ',', '.') }} dari 5">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="star {{ $i <= $full ? 'is-on' : ($i === $full + 1 && $hasHalf ? 'is-half' : '') }}" aria-hidden="true">
            <use href="#i-star"/>
        </svg>
    @endfor
</span>
