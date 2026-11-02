@extends('layouts.app')

@section('title', $title.' — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-3xl">
        <header class="rounded-[1.75rem] bg-ink-50 p-6 sm:p-9">
            <span class="text-xs font-medium text-brand-600">Informasi</span>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink-900 sm:text-3xl">{{ $title }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ config('app.name') }} adalah proyek demo. Pembayaran dan pengiriman berjalan dalam mode simulasi, jadi tidak ada uang atau barang sungguhan.</p>
        </header>

        <div class="mt-6 space-y-6 text-sm leading-relaxed text-ink-700">
            @foreach ($sections as $heading => $body)
                <section>
                    <h2 class="text-base font-semibold text-ink-900">{{ $heading }}</h2>
                    <p class="mt-1.5">{{ $body }}</p>
                </section>
            @endforeach
        </div>
    </div>
</div>
@endsection
