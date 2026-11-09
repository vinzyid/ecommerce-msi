@php
    $rupiah = fn ($value) => 'Rp'.number_format((float) $value, 0, ',', '.');
    $destination = collect([$order->address, $order->district, $order->city, $order->province, $order->postal_code])->filter()->implode(', ');
    $forPdf = $forPdf ?? false;
    $logoPath = public_path('images/logo-email.png');
    // Email: logo inline (CID) karena Gmail tidak menampilkan SVG. PDF: logo base64.
    $logo = match (true) {
        ! is_file($logoPath) => null,
        $forPdf => 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)),
        isset($message) => $message->embed($logoPath),
        default => null,
    };
    $muted = 'color:#6e6e73;';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:{{ $forPdf ? '#ffffff' : '#f5f5f7' }};font-family:{{ $forPdf ? 'DejaVu Sans, sans-serif' : "-apple-system,'Segoe UI',Inter,Arial,sans-serif" }};color:#2c2c2e;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $forPdf ? '#ffffff' : '#f5f5f7' }};padding:{{ $forPdf ? '0' : '24px 12px' }};">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #ebebef;border-radius:16px;">
                <tr>
                    <td style="padding:24px 24px 16px;border-bottom:1px solid #ebebef;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td valign="middle">
                                    @if ($logo)
                                        <img src="{{ $logo }}" width="40" height="40" alt="" style="display:inline-block;vertical-align:middle;border:0;">
                                    @endif
                                    <span style="display:inline-block;vertical-align:middle;margin-left:8px;font-size:20px;font-weight:bold;color:#1d1d1f;">{{ config('app.name') }}</span>
                                    <div style="margin-top:4px;font-size:12px;{{ $muted }}">Gaming, diecast &amp; hobi</div>
                                </td>
                                <td valign="top" align="right">
                                    <div style="font-size:20px;font-weight:bold;color:#1d1d1f;">INVOICE</div>
                                    <div style="font-family:Menlo,Consolas,monospace;font-size:12px;color:#545458;">{{ $order->order_number }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px 24px 0;font-size:14px;line-height:1.5;">
                        <p style="margin:0 0 12px;">Halo, <strong>{{ $order->customer_name }}</strong>!</p>
                        <p style="margin:0;">
                            @if ($order->paymentStatusLabel() === 'Lunas')
                                Terima kasih, pembayaran pesananmu sudah kami terima. Berikut struk belanjamu.
                            @else
                                Berikut struk untuk pesananmu.
                            @endif
                            @unless ($forPdf)
                                Struk dalam bentuk PDF juga kami lampirkan, siap dibuka dan dicetak.
                            @endunless
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px 24px 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
                            <tr>
                                <td valign="top" style="padding-right:12px;">
                                    <div style="font-size:12px;font-weight:bold;{{ $muted }}">Ditagihkan kepada</div>
                                    <div style="margin-top:4px;font-weight:bold;color:#1d1d1f;">{{ $order->customer_name }}</div>
                                    <div style="color:#545458;">{{ $order->phone }}</div>
                                    <div style="color:#545458;">{{ $destination }}</div>
                                </td>
                                <td valign="top" align="right">
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:13px;">
                                        <tr><td style="{{ $muted }}padding:1px 12px 1px 0;white-space:nowrap;">Tanggal</td><td align="right" style="white-space:nowrap;">{{ $order->ordered_at->translatedFormat('d F Y') }}</td></tr>
                                        <tr><td style="{{ $muted }}padding:1px 12px 1px 0;white-space:nowrap;">Status pesanan</td><td align="right">{{ $order->statusLabel() }}</td></tr>
                                        <tr><td style="{{ $muted }}padding:1px 12px 1px 0;white-space:nowrap;">Metode bayar</td><td align="right">{{ $order->paymentLabel() }}</td></tr>
                                        <tr><td style="{{ $muted }}padding:1px 12px 1px 0;white-space:nowrap;">Status bayar</td><td align="right" style="font-weight:bold;color:#1d1d1f;">{{ $order->paymentStatusLabel() }}</td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px 24px 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse;">
                            <tr>
                                <th align="left" style="padding:8px 8px 8px 0;border-bottom:1px solid #d9d9de;font-size:12px;{{ $muted }}">Produk</th>
                                <th align="right" style="padding:8px 8px;border-bottom:1px solid #d9d9de;font-size:12px;{{ $muted }}">Jml</th>
                                <th align="right" style="padding:8px 0;border-bottom:1px solid #d9d9de;font-size:12px;{{ $muted }}">Subtotal</th>
                            </tr>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td valign="top" style="padding:10px 8px 10px 0;border-bottom:1px solid #ebebef;">
                                        <div style="font-weight:bold;color:#1d1d1f;">{{ $item->product_name }}</div>
                                        <div style="font-size:12px;{{ $muted }}">{{ $rupiah($item->price) }} / pcs &middot; SKU {{ $item->sku }}</div>
                                    </td>
                                    <td valign="top" align="right" style="padding:10px 8px;border-bottom:1px solid #ebebef;">{{ $item->quantity }}</td>
                                    <td valign="top" align="right" style="padding:10px 0;border-bottom:1px solid #ebebef;white-space:nowrap;">{{ $rupiah($item->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0;" align="right">
                        <table role="presentation" align="right" cellpadding="0" cellspacing="0" style="font-size:13px;width:260px;">
                            <tr><td style="{{ $muted }}padding:2px 16px 2px 0;">Subtotal</td><td align="right">{{ $rupiah($order->subtotal) }}</td></tr>
                            @if ((int) $order->discount > 0)
                                <tr><td style="{{ $muted }}padding:2px 16px 2px 0;">Diskon{{ $order->voucher_code ? " ({$order->voucher_code})" : '' }}</td><td align="right">-{{ $rupiah($order->discount) }}</td></tr>
                            @endif
                            <tr><td style="{{ $muted }}padding:2px 16px 2px 0;">Ongkir</td><td align="right">{{ (float) $order->shipping_cost > 0 ? $rupiah($order->shipping_cost) : 'Gratis' }}</td></tr>
                            <tr>
                                <td style="padding:8px 16px 0 0;border-top:1px solid #d9d9de;font-size:15px;font-weight:bold;color:#1d1d1f;">Total</td>
                                <td align="right" style="padding-top:8px;border-top:1px solid #d9d9de;font-size:15px;font-weight:bold;color:#1d1d1f;">{{ $rupiah($order->total) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @unless ($forPdf)
                <tr>
                    <td style="padding:24px;" align="center">
                        <a href="{{ route('orders.invoice', $order) }}" style="display:inline-block;background:#1d1d1f;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:10px 22px;border-radius:999px;">Buka pesanan di {{ config('app.name') }}</a>
                    </td>
                </tr>
                @else
                <tr><td style="height:24px;"></td></tr>
                @endunless

                <tr>
                    <td style="padding:16px 24px 24px;border-top:1px solid #ebebef;font-size:12px;{{ $muted }}">
                        {{ $forPdf ? 'Dokumen ini dibuat otomatis oleh' : 'Email ini dikirim otomatis oleh' }} {{ config('app.name') }}. Pembayaran pada aplikasi ini berjalan dalam mode simulasi.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
