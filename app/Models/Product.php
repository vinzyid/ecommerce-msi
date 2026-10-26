<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'compare_at_price',
        'badge',
        'weight_grams',
        'material',
        'color',
        'dimensions',
        'stock',
        'image_url',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'weight_grams' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Diskon dianggap ada bila potongannya minimal 1% (hindari label "-0%").
     */
    public function hasDiscount(): bool
    {
        return $this->rawDiscountPercent() >= 1;
    }

    /**
     * Persentase diskon untuk ditampilkan, dibatasi 1–99% agar tidak pernah
     * tertulis "-100%" untuk produk yang tidak gratis.
     */
    public function discountPercent(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) min(99, max(1, round($this->rawDiscountPercent())));
    }

    private function rawDiscountPercent(): float
    {
        $compare = (float) $this->compare_at_price;
        $price = (float) $this->price;

        if ($this->compare_at_price === null || $compare <= 0 || $compare <= $price) {
            return 0.0;
        }

        return (1 - $price / $compare) * 100;
    }

    public function averageRating(): ?float
    {
        $average = $this->reviews()->where('is_approved', true)->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
