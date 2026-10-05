@props(['status'])

@php
    $map = [
        'pending' => ['bg-accent-500/15 text-accent-600', 'Menunggu'],
        'processing' => ['bg-brand-50 text-brand-700', 'Diproses'],
        'shipped' => ['bg-brand-100 text-brand-800', 'Dikirim'],
        'completed' => ['bg-success-500/15 text-success-600', 'Selesai'],
        'cancelled' => ['bg-danger-500/15 text-danger-600', 'Dibatalkan'],
    ];
    [$classes] = $map[$status] ?? ['bg-ink-100 text-ink-600', $status];
    $label = $label ?? ($map[$status][1] ?? ucfirst($status));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold $classes"]) }}>
    {{ $label }}
</span>
