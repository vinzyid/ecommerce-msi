@php
    $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
    $destination = collect([$order->address, $order->district, $order->city, $order->province, $order->postal_code])->filter()->implode(', ');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Invoice {{ $order->order_number }} — {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page { margin: 16mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-ink-50 text-ink-800 print:bg-white">
    <div class="mx-auto max-w-3xl px-4 py-6 print:max-w-none print:p-0">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
            <a href="{{ route('orders.show', $order) }}" class="text-sm font-medium text-ink-600 hover:text-ink-900">&larr; Kembali ke pesanan</a>
            <button type="button" onclick="window.print()"
                    class="rounded-full bg-ink-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-ink-700">
                Cetak / Simpan PDF
            </button>
        </div>

        <main class="rounded-2xl border border-ink-100 bg-white p-6 sm:p-8 print:rounded-none print:border-0 print:p-0">
            <header class="flex flex-wrap items-start justify-between gap-4 border-b border-ink-100 pb-5">
                <div>
                    <p class="text-lg font-semibold text-ink-900">{{ config('app.name') }}</p>
                    <p class="mt-0.5 text-xs text-ink-500">Gaming, diecast &amp; hobi</p>
                </div>
                <div class="text-right">
                    <h1 class="text-2xl font-semibold tracking-tight text-ink-900">INVOICE</h1>
                    <p class="mt-0.5 font-mono text-xs text-ink-600">{{ $order->order_number }}</p>
                </div>
            </header>

            <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2 print:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold text-ink-500">Ditagihkan kepada</dt>
                    <dd class="mt-1 font-semibold text-ink-900">{{ $order->customer_name }}</dd>
                    <dd class="text-ink-600">{{ $order->phone }}</dd>
                    <dd class="text-ink-600">{{ $destination }}</dd>
                </div>
                <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 sm:justify-self-end print:justify-self-end">
                    <dt class="text-ink-500">Tanggal pesanan</dt>
                    <dd class="text-right text-ink-900">{{ $order->ordered_at->translatedFormat('d F Y') }}</dd>
                    <dt class="text-ink-500">Status pesanan</dt>
                    <dd class="text-right text-ink-900">{{ $order->statusLabel() }}</dd>
                    <dt class="text-ink-500">Metode bayar</dt>
                    <dd class="text-right text-ink-900">{{ $order->paymentLabel() }}</dd>
                    <dt class="text-ink-500">Status bayar</dt>
                    <dd class="text-right font-semibold text-ink-900">{{ $order->paymentStatusLabel() }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 text-xs text-ink-500">
                            <th scope="col" class="py-2 pr-3 font-semibold">Produk</th>
                            <th scope="col" class="hidden py-2 pr-3 text-right font-semibold sm:table-cell print:table-cell">Harga</th>
                            <th scope="col" class="hidden py-2 pr-3 text-right font-semibold sm:table-cell print:table-cell">Jumlah</th>
                            <th scope="col" class="py-2 text-right font-semibold">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b border-ink-100 align-top">
                                <td class="py-3 pr-3">
                                    <span class="font-medium text-ink-900">{{ $item->product_name }}</span>
                                    <span class="block text-xs text-ink-500">SKU {{ $item->sku }}</span>
                                    <span class="block text-xs tabular-nums text-ink-500 sm:hidden print:hidden">{{ $item->quantity }} &times; {{ $rupiah($item->price) }}</span>
                                </td>
                                <td class="hidden py-3 pr-3 text-right tabular-nums sm:table-cell print:table-cell">{{ $rupiah($item->price) }}</td>
                                <td class="hidden py-3 pr-3 text-right tabular-nums sm:table-cell print:table-cell">{{ $item->quantity }}</td>
                                <td class="py-3 text-right tabular-nums">{{ $rupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <dl class="ml-auto mt-4 max-w-xs space-y-1.5 text-sm">
                <div class="flex justify-between gap-6">
                    <dt class="text-ink-500">Subtotal</dt>
                    <dd class="tabular-nums">{{ $rupiah($order->subtotal) }}</dd>
                </div>
                @if ((int) $order->discount > 0)
                    <div class="flex justify-between gap-6">
                        <dt class="text-ink-500">Diskon{{ $order->voucher_code ? " ({$order->voucher_code})" : '' }}</dt>
                        <dd class="tabular-nums">-{{ $rupiah($order->discount) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-6">
                    <dt class="text-ink-500">Ongkir</dt>
                    <dd class="tabular-nums">{{ (float) $order->shipping_cost > 0 ? $rupiah($order->shipping_cost) : 'Gratis' }}</dd>
                </div>
                <div class="flex justify-between gap-6 border-t border-ink-200 pt-2 text-base font-semibold text-ink-900">
                    <dt>Total</dt>
                    <dd class="tabular-nums">{{ $rupiah($order->total) }}</dd>
                </div>
            </dl>

            <p class="mt-8 border-t border-ink-100 pt-4 text-xs text-ink-500">
                Dokumen ini dibuat otomatis oleh {{ config('app.name') }}. Pembayaran pada aplikasi ini berjalan dalam mode simulasi.
            </p>
        </main>
    </div>
</body>
</html>
