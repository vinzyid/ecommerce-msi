<?php

namespace App\Models;

use App\Models\Concerns\RefreshesStoreCaches;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use RefreshesStoreCaches, HasFactory;

    public const TYPES = ['percent', 'fixed'];

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_spend',
        'max_discount',
        'usage_limit',
        'per_user_limit',
        'used_count',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_spend' => 'integer',
            'max_discount' => 'integer',
            'usage_limit' => 'integer',
            'per_user_limit' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Pesanan yang dibatalkan tidak dihitung, jadi kuotanya kembali ke pengguna.
     */
    public function isUsableBy(User $user): bool
    {
        if (! $this->isUsable()) {
            return false;
        }

        return $this->per_user_limit === null || $user->orders()
            ->where('voucher_id', $this->id)
            ->where('status', '!=', 'cancelled')
            ->count() < $this->per_user_limit;
    }

    public function discountFor(int $subtotal): int
    {
        if ($subtotal < $this->min_spend) {
            return 0;
        }

        $discount = $this->type === 'fixed'
            ? $this->value
            : (int) floor($subtotal * $this->value / 100);

        if ($this->max_discount !== null) {
            $discount = min($discount, $this->max_discount);
        }

        return min($discount, $subtotal);
    }
}
