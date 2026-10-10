<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'phone',
        'address',
        'province',
        'city',
        'district',
        'postal_code',
        'shipping_method',
        'notes',
        'payment_method',
        'voucher_id',
        'voucher_code',
        'courier',
        'tracking_number',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'status',
        'ordered_at',
        'shipped_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'integer',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'ordered_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->district, $this->city, $this->province, $this->postal_code])
            ->filter()
            ->implode(', ');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ][$this->status] ?? $this->status;
    }

    public function paymentLabel(): string
    {
        return $this->payment_method === 'cod' ? 'Bayar di tempat' : 'Transfer bank';
    }

    /**
     * Timeline pelacakan kurir bertahap ala Tokopedia / Shopee.
     *
     * @return array<int, array{step: string, title: string, desc: string, time: ?string, done: bool, current: bool}>
     */
    public function trackingTimeline(): array
    {
        $status = $this->status;
        $courier = $this->courier ?? ($this->shipping_method === 'express' ? 'JNE YES (Kilat)' : 'JNE Regular');
        $resi = $this->tracking_number ?? '-';
        $city = $this->city ?? 'alamat tujuan';

        $orderedTime = $this->ordered_at?->translatedFormat('d M Y, H:i') ?? '-';
        $shippedTime = $this->shipped_at?->translatedFormat('d M Y, H:i') ?? ($this->ordered_at ? $this->ordered_at->addHours(4)->translatedFormat('d M Y, H:i') : '-');
        $transitTime = $this->shipped_at ? $this->shipped_at->addHours(8)->translatedFormat('d M Y, H:i') : null;
        $deliveryTime = $this->shipped_at ? $this->shipped_at->addHours(18)->translatedFormat('d M Y, H:i') : null;
        $completedTime = $this->completed_at?->translatedFormat('d M Y, H:i') ?? ($this->shipped_at ? $this->shipped_at->addHours(24)->translatedFormat('d M Y, H:i') : '-');

        $isPending = $status === 'pending';
        $isProcessing = $status === 'processing';
        $isShipped = $status === 'shipped';
        $isCompleted = $status === 'completed';

        return [
            [
                'step' => 'created',
                'title' => 'Pesanan Dibuat',
                'desc' => $this->payment_method === 'cod' ? 'Pesanan COD berhasil dibuat dan menunggu konfirmasi.' : 'Pembayaran Virtual Account berhasil diverifikasi.',
                'time' => $orderedTime,
                'done' => true,
                'current' => $isPending,
            ],
            [
                'step' => 'processing',
                'title' => 'Sedang Dikemas Penjual',
                'desc' => 'VinzyPlay Official Store sedang menyiapkan dan memeriksa kondisi barang sebelum diserahkan ke kurir.',
                'time' => $isPending ? null : $orderedTime,
                'done' => ! $isPending,
                'current' => $isProcessing,
            ],
            [
                'step' => 'shipped',
                'title' => "Diserahkan ke Kurir ({$courier})",
                'desc' => "Paket telah diterima agen logistik Yogyakarta dengan nomor resi {$resi}.",
                'time' => ($isShipped || $isCompleted) ? $shippedTime : null,
                'done' => $isShipped || $isCompleted,
                'current' => $isShipped,
            ],
            [
                'step' => 'transit',
                'title' => "Dalam Perjalanan ke {$city}",
                'desc' => "Paket diberangkatkan dari Hub Logistik transit menuju kantor kurir destinasi terdekat.",
                'time' => ($isShipped || $isCompleted) ? $transitTime : null,
                'done' => $isShipped || $isCompleted,
                'current' => false,
            ],
            [
                'step' => 'delivering',
                'title' => 'Kurir Menuju Alamatmu',
                'desc' => "Kurir sedang mengantarkan paket menuju alamat penerima ({$this->customer_name}).",
                'time' => ($isShipped || $isCompleted) ? $deliveryTime : null,
                'done' => $isCompleted,
                'current' => $isShipped,
            ],
            [
                'step' => 'completed',
                'title' => 'Pesanan Diterima & Selesai',
                'desc' => 'Paket telah berhasil diterima oleh pemesan. Transaksi selesai.',
                'time' => $isCompleted ? $completedTime : null,
                'done' => $isCompleted,
                'current' => $isCompleted,
            ],
        ];
    }
}
