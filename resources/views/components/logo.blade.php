@props(['wordmark' => true, 'inverted' => false])
{{-- Logo VinzyPlay: tanda "V" yang sekaligus tombol play, plus wordmark --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <svg class="h-8 w-8 shrink-0 {{ $inverted ? 'text-white' : 'text-ink-900' }}" viewBox="0 0 32 32" aria-hidden="true">
        <rect width="32" height="32" rx="9" fill="currentColor"/>
        <path d="M10 10.5 16 22l6-11.5" fill="none" stroke="{{ $inverted ? '#1d1d1f' : '#fff' }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="22.5" cy="10.5" r="1.9" fill="#0071e3"/>
    </svg>
    @if ($wordmark)
        <span class="text-[17px] font-semibold tracking-tight {{ $inverted ? 'text-white' : 'text-ink-900' }}">VinzyPlay</span>
    @endif
</span>
