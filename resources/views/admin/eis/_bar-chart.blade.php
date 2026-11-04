{{-- Grafik batang satu seri. $points: key, label, fullLabel, partial, value, display --}}
@php
    $max = max(1, collect($points)->max('value'));
    $highest = collect($points)->sortByDesc('value')->keys()->first();
@endphp
<article class="min-w-0 rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
    <div class="flex items-start justify-between gap-3">
        <h2 class="text-sm font-semibold text-ink-900">{{ $title }}</h2>
        <p class="text-xs text-ink-500">{{ $note }}</p>
    </div>
    <div class="mt-5 flex h-52 items-end gap-1.5 border-b border-ink-200 sm:gap-2.5" role="img" aria-label="{{ $title }}, rinciannya ada pada tabel di bawah grafik">
        @foreach ($points as $i => $point)
            @php $showValue = $point['value'] > 0 && ($i === $highest || $i === count($points) - 1); @endphp
            <a href="{{ route('admin.eis.show', $point['key']) }}" tabindex="-1"
               class="group relative flex h-full min-w-0 flex-1 flex-col items-center justify-end">
                @if ($showValue)
                    <span class="mb-1 whitespace-nowrap text-[11px] font-medium tabular-nums text-ink-700">{{ $point['display'] }}</span>
                @endif
                @if ($point['value'] > 0)
                    <span @class([
                        'w-full max-w-14 rounded-t transition-colors',
                        'bg-brand-600 group-hover:bg-brand-800' => ! $point['partial'],
                        'bg-brand-200 group-hover:bg-brand-300' => $point['partial'],
                    ]) style="height: {{ max(1.5, round($point['value'] / $max * 82, 1)) }}%"></span>
                @else
                    <span class="h-0.5 w-full max-w-14"></span>
                @endif
                <span class="pointer-events-none absolute top-0 z-10 hidden w-max rounded-lg bg-ink-900 px-2.5 py-1.5 text-xs text-white shadow-lift group-hover:block">
                    {{ $point['fullLabel'] }}@if ($point['partial']) (berjalan)@endif: {{ $point['display'] }}
                </span>
            </a>
        @endforeach
    </div>
    <div class="mt-2 flex gap-1.5 sm:gap-2.5">
        @foreach ($points as $point)
            <span class="min-w-0 flex-1 text-center text-[11px] text-ink-500">{{ $point['label'] }}</span>
        @endforeach
    </div>
</article>
