<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\User;
use App\Models\Voucher;

class CartCalculator
{
    public const FREE_SHIPPING_MINIMUM = 300000;

    public const SHIPPING_COST = 15000;

    public const EXPRESS_SHIPPING_COST = 25000;

    /**
     * @param  array<int>|null  $itemIds
     * @return array{subtotal:int, discount:int, shipping:int, total:int, voucher:?Voucher}
     */
    public function summarize(User $user, ?string $voucherCode = null, string $shippingMethod = 'regular', ?array $itemIds = null): array
    {
        $query = $user->cartItems()->with('product');

        if (! empty($itemIds)) {
            $query->whereIn('id', $itemIds);
        }

        $subtotal = (int) $query->get()
            ->sum(fn (CartItem $item) => (int) $item->product->price * $item->quantity);

        $voucher = $voucherCode
            ? Voucher::query()->where('code', $voucherCode)->first()
            : null;

        $discount = 0;

        if ($voucher && $voucher->isUsable()) {
            $discount = $voucher->discountFor($subtotal);
        } else {
            $voucher = null;
        }

        $shipping = $this->shippingCost($subtotal, $discount, $shippingMethod);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => max(0, $subtotal - $discount) + $shipping,
            'voucher' => $voucher,
        ];
    }

    public function shippingCost(int $subtotal, int $discount = 0, string $shippingMethod = 'regular'): int
    {
        if ($shippingMethod === 'express') {
            return self::EXPRESS_SHIPPING_COST;
        }

        return ($subtotal - $discount) >= self::FREE_SHIPPING_MINIMUM ? 0 : self::SHIPPING_COST;
    }

    public static function shippingLabel(string $method): string
    {
        return $method === 'express' ? 'Pengiriman Kilat' : 'Pengiriman Reguler';
    }
}
