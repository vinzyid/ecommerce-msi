@extends('layouts.app')

@section('title', $order->order_number.' — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-4xl">
        <nav class="flex items-center gap-2 text-sm text-ink-500" aria-label="Breadcrumb">
            <a href="{{ route('orders.index') }}" class="hover:text-brand-700">Pesanan</a>
            <span class="text-ink-300">/</span>
            <b class="font-semibold text-ink-700">{{ $order->order_number }}</b>
        </nav>

        <header class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-brand-600">Pesanan / {{ $order->ordered_at->format('d.m.Y') }}</span>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink-900">{{ $order->order_number }}</h1>
            </div>
            <x-status-badge :status="$order->status" class="!px-3.5 !py-1.5 !text-sm" />
        </header>

        {{-- Stepper Status Pesanan (Tokopedia / Shopee Style) --}}
        @php
            $currentStatus = $order->status;
            $steps = [
                ['pending', 'Menunggu Bayar', 'i-clock'],
                ['processing', 'Diproses Penjual', 'i-package'],
                ['shipped', 'Sedang Dikirim', 'i-truck'],
                ['completed', 'Pesanan Selesai', 'i-check-circle'],
            ];
            $statusIndex = match ($currentStatus) {
                'pending' => 0,
                'processing' => 1,
                'shipped' => 2,
                'completed' => 3,
                default => -1,
            };
        @endphp
        @if ($statusIndex >= 0)
            <div class="mt-5 rounded-2xl border border-ink-100 bg-white p-4 shadow-card sm:p-5">
                <ol class="flex items-center justify-between gap-2 text-xs font-semibold">
                    @foreach ($steps as $i => [$code, $label, $icon])
                        @php
                            $isDone = $statusIndex >= $i;
                            $isCurrent = $statusIndex === $i;
                        @endphp
                        <li class="flex flex-1 items-center gap-2 {{ $isDone ? ($isCurrent ? 'text-brand-600 font-bold' : 'text-emerald-600') : 'text-ink-400' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs transition {{ $isDone ? ($isCurrent ? 'bg-brand-600 text-white shadow-sm ring-4 ring-brand-100' : 'bg-emerald-600 text-white') : 'bg-ink-100 text-ink-400' }}">
                                <svg class="h-4 w-4"><use href="#{{ $icon }}"/></svg>
                            </span>
                            <span class="hidden sm:inline truncate">{{ $label }}</span>
                        </li>
                        @if ($i < count($steps) - 1)
                            <div class="h-0.5 flex-1 transition {{ $statusIndex > $i ? 'bg-emerald-500' : 'bg-ink-200' }}"></div>
                        @endif
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_320px]">
            <section class="space-y-5">
                {{-- Kartu Instruksi Pembayaran (Tokopedia / Shopee Style) --}}
                @if (in_array($order->payment_method, ['bank_transfer', 'transfer']) && $order->status === 'pending')
                    <div class="rounded-2xl border border-brand-200 bg-gradient-to-br from-brand-50/80 to-white p-5 shadow-card">
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white shadow-xs">
                                    <svg class="h-5 w-5"><use href="#i-credit-card"/></svg>
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-ink-900">Instruksi Pembayaran Transfer Bank</h3>
                                    <p class="text-xs text-ink-500">Silakan transfer sesuai nominal total di bawah</p>
                                </div>
                            </div>
                            <span class="hidden shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700 sm:inline-block">Belum Dibayar</span>
                        </div>

                        <div class="mt-4 rounded-xl border border-brand-200/70 bg-white p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="text-[11px] font-semibold uppercase tracking-wider text-ink-400">Bank Central Asia (BCA) Virtual Account</span>
                                    <div class="mt-1 font-mono text-lg font-black tracking-wider text-brand-700">8801 2345 6789 00</div>
                                    <p class="text-[11px] text-ink-500">a.n. PT VinzyPlay Indonesia</p>
                                </div>
                                <button type="button" data-copy-voucher="88012345678900"
                                        class="rounded-lg border border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 transition hover:bg-brand-600 hover:text-white">
                                    Salin Rekening
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between text-xs text-ink-600">
                            <span>Total yang harus dibayar:</span>
                            <strong class="font-mono text-base font-black text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                        </div>

                        {{-- Tombol Simulasi Pembayaran (Mode Demo / Sandbox) --}}
                        <div class="mt-4 rounded-xl border border-dashed border-brand-300 bg-brand-50/50 p-3.5">
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold text-brand-700">
                                <svg class="h-3.5 w-3.5"><use href="#i-bolt"/></svg>
                                Mode Demo / Sandbox
                            </p>
                            <p class="mt-0.5 text-[11px] text-ink-500">Tidak perlu transfer sungguhan. Klik tombol di bawah untuk mensimulasikan pembayaran yang berhasil.</p>
                            <form method="POST" action="{{ route('orders.simulatePayment', $order) }}" class="mt-2.5"
                                  onsubmit="return confirm('Simulasikan pembayaran Virtual Account sebagai BERHASIL? Pesanan akan otomatis ditandai LUNAS.');">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.99]">
                                    <svg class="h-4.5 w-4.5"><use href="#i-check-circle"/></svg>
                                    Bayar &amp; Tandai Lunas (Simulasi)
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif (in_array($order->payment_method, ['bank_transfer', 'transfer']) && in_array($order->status, ['processing', 'shipped', 'completed']))
                    {{-- Kartu Sukses Pembayaran VA --}}
                    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 to-white p-5 shadow-card">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                                <svg class="h-6 w-6"><use href="#i-check-circle"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-emerald-800">Pembayaran Virtual Account Berhasil</h3>
                                <p class="text-xs text-emerald-700">Pesanan Anda sudah LUNAS dan sedang diproses penjual.</p>
                            </div>
                        </div>

                        <dl class="mt-4 grid gap-2 rounded-xl border border-emerald-200/70 bg-white p-4 text-xs sm:grid-cols-3">
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-400">Status Pembayaran</dt>
                                <dd class="mt-0.5 font-bold text-emerald-700">LUNAS ✓</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-400">Metode</dt>
                                <dd class="mt-0.5 font-bold text-ink-800">BCA Virtual Account</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-400">Nominal Dibayar</dt>
                                <dd class="mt-0.5 font-mono font-black text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </div>
                @elseif ($order->payment_method === 'cod')
                    <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                            <svg class="h-5 w-5"><use href="#i-truck"/></svg>
                        </span>
                        <div class="text-xs text-amber-900">
                            <strong class="font-bold">Metode Bayar di Tempat (COD)</strong>
                            <p class="mt-0.5 text-amber-700">Siapkan uang pas sejumlah <b>Rp{{ number_format($order->total, 0, ',', '.') }}</b> saat kurir menyerahkan paket ke alamatmu.</p>
                        </div>
                    </div>
                @endif

                {{-- Aksi Tahapan Pesanan (Demo Pemrosesan & Pengiriman) --}}
                @if ($order->status === 'processing')
                    <div class="rounded-2xl border border-dashed border-brand-300 bg-brand-50/50 p-4 shadow-card">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white">
                                <svg class="h-5 w-5"><use href="#i-package"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-ink-900">Penjual sedang mengemas pesananmu</p>
                                <p class="mt-0.5 text-xs text-ink-500">Mode demo: klik tombol di bawah untuk mensimulasikan penjual menyerahkan paket ke kurir & menerbitkan nomor resi.</p>
                                <form method="POST" action="{{ route('orders.simulateShip', $order) }}" class="mt-2.5"
                                      onsubmit="return confirm('Simulasikan penjual mengirim paket ini ke kurir? Nomor resi akan diterbitkan otomatis.');">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700 active:scale-[0.99]">
                                        <svg class="h-4.5 w-4.5"><use href="#i-truck"/></svg>
                                        Kirim Pesanan &amp; Terbitkan Resi (Simulasi)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @elseif ($order->status === 'shipped')
                    <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-4 shadow-card">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                <svg class="h-5 w-5"><use href="#i-box"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-ink-900">Sudah menerima paketnya?</p>
                                <p class="mt-0.5 text-xs text-ink-500">Konfirmasi jika paket telah tiba dengan aman agar pesanan dinyatakan selesai dan kamu bisa memberi ulasan.</p>
                                <form method="POST" action="{{ route('orders.complete', $order) }}" class="mt-2.5"
                                      onsubmit="return confirm('Konfirmasi bahwa paket sudah kamu terima dengan baik? Pesanan akan ditandai SELESAI.');">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.99]">
                                        <svg class="h-4.5 w-4.5"><use href="#i-check-circle"/></svg>
                                        Pesanan Diterima (Selesaikan Pesanan)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @elseif ($order->status === 'completed')
                    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 to-white p-4 shadow-card">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                <svg class="h-5 w-5"><use href="#i-star-filled"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-emerald-800">Pesanan Selesai! ⭐</p>
                                <p class="mt-0.5 text-xs text-emerald-700">Terima kasih telah berbelanja. Bagikan pengalamanmu dengan menulis ulasan produk yang sudah diterima.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Panel Lacak Pengiriman (Tokopedia / Shopee Style) --}}
                @if (in_array($order->status, ['shipped', 'completed']))
                    @php $timeline = $order->trackingTimeline(); @endphp
                    <div class="overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card" id="lacak">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 bg-brand-600 px-5 py-4 text-white">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15">
                                    <svg class="h-5 w-5"><use href="#i-truck"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-sm font-bold">Lacak Pengiriman</h2>
                                    <p class="text-[11px] text-white/80">Status paket real-time</p>
                                </div>
                            </div>
                            @if ($order->tracking_number)
                                <button type="button" data-copy-voucher="{{ $order->tracking_number }}"
                                        class="rounded-lg bg-white/15 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-white/25">
                                    Salin Resi
                                </button>
                            @endif
                        </div>

                        <div class="grid gap-3 border-b border-ink-100 bg-ink-50/60 px-5 py-3.5 text-xs sm:grid-cols-3">
                            <div>
                                <dt class="font-semibold uppercase tracking-wider text-ink-400">Kurir</dt>
                                <dd class="mt-0.5 font-bold text-ink-800">{{ $order->courier ?? 'JNE Regular' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold uppercase tracking-wider text-ink-400">Nomor Resi</dt>
                                <dd class="mt-0.5 font-mono font-bold text-brand-700">{{ $order->tracking_number ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold uppercase tracking-wider text-ink-400">Estimasi Tiba</dt>
                                <dd class="mt-0.5 font-bold text-ink-800">
                                    {{ $order->shipped_at ? $order->shipped_at->copy()->addDays(3)->translatedFormat('d M Y') : '-' }}
                                </dd>
                            </div>
                        </div>

                        {{-- Timeline Riwayat --}}
                        <ol class="space-y-0 px-5 py-5">
                            @foreach (array_reverse($timeline) as $i => $event)
                                @php $isLatest = $i === 0 && $event['done']; @endphp
                                <li class="relative flex gap-3.5 {{ ! $loop->last ? 'pb-5' : '' }}">
                                    {{-- Garis penghubung --}}
                                    @if (! $loop->last)
                                        <span class="absolute left-[13px] top-7 h-full w-0.5 {{ $event['done'] ? 'bg-emerald-500' : 'bg-ink-200' }}"></span>
                                    @endif
                                    <span class="relative z-10 mt-0.5 flex h-6.5 w-6.5 shrink-0 items-center justify-center rounded-full ring-4 {{ $event['done'] ? 'bg-emerald-600 text-white ring-emerald-100' : ($event['current'] ? 'bg-brand-600 text-white ring-brand-100' : 'bg-ink-200 text-white ring-ink-50') }}">
                                        <svg class="h-3.5 w-3.5"><use href="#i-check"/></svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-xs font-bold {{ $event['done'] ? 'text-ink-900' : 'text-ink-400' }}">{{ $event['title'] }}</p>
                                            @if ($isLatest)
                                                <span class="rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-black uppercase text-white">Terbaru</span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 text-[11px] leading-relaxed {{ $event['done'] ? 'text-ink-500' : 'text-ink-300' }}">{{ $event['desc'] }}</p>
                                        @if ($event['time'])
                                            <p class="mt-0.5 text-[10px] font-semibold text-ink-400">{{ $event['time'] }} WIB</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                    <div class="divide-y divide-ink-100">
                        @foreach ($order->items as $item)
                            <div class="flex items-center gap-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-ink-400">{{ $item->sku }}</div>
                                    <div class="truncate text-sm font-bold text-ink-900">{{ $item->product_name }}</div>
                                    <div class="mt-0.5 text-xs text-ink-500">{{ $item->quantity }} &times; Rp{{ number_format($item->price, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-right">
                                    <b class="block text-sm font-extrabold text-ink-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</b>
                                    @if ($order->status === 'completed' && $item->product)
                                        <a href="{{ route('products.show', $item->product) }}#ulasan"
                                           class="mt-1 inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:text-brand-700">
                                            Beri Ulasan &rarr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <dl class="mt-4 space-y-2.5 border-t border-ink-100 pt-4 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Subtotal</dt>
                            <dd class="font-semibold text-ink-800">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                        </div>
                        @if ((int) $order->discount > 0)
                            <div class="flex justify-between text-success-600">
                                <dt class="font-semibold">Diskon {{ $order->voucher_code }}</dt>
                                <dd class="font-bold">-Rp{{ number_format($order->discount, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Ongkir</dt>
                            <dd class="font-semibold text-ink-800">{{ (int) $order->shipping_cost === 0 ? 'Gratis' : 'Rp'.number_format($order->shipping_cost, 0, ',', '.') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-end justify-between border-t border-ink-100 pt-4">
                        <span class="text-sm font-semibold text-ink-600">Total</span>
                        <strong class="text-xl font-extrabold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </section>

            <aside class="h-fit rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                <h2 class="text-base font-extrabold text-ink-900">Informasi Pengiriman</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Penerima</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Telepon</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ $order->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Alamat</dt>
                        <dd class="mt-0.5 leading-relaxed text-ink-700">{{ $order->fullAddress() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Pengiriman</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ \App\Support\CartCalculator::shippingLabel($order->shipping_method ?? 'regular') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Pembayaran</dt>
                        <dd class="mt-0.5 font-semibold text-ink-800">{{ $order->paymentLabel() }}</dd>
                    </div>
                    @if ($order->notes)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ink-400">Catatan</dt>
                            <dd class="mt-0.5 leading-relaxed text-ink-700">{{ $order->notes }}</dd>
                        </div>
                    @endif
                </dl>

                <a href="{{ route('orders.index') }}" class="mt-5 block rounded-xl border border-ink-200 px-4 py-2.5 text-center text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                    &larr; Semua pesanan
                </a>
            </aside>
        </div>
    </div>
</div>
@endsection
