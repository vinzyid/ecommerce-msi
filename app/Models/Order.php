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
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'status',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'integer',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'ordered_at' => 'datetime',
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
}
