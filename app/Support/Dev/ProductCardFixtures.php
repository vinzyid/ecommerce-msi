<?php

namespace App\Support\Dev;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Data uji kartu produk (khusus dev). Nilai "worst case" mengikuti batas
 * validasi asli: nama 120, badge 30, kategori 80, harga 9.999.999.999.
 */
class ProductCardFixtures
{
    public const STATES = [
        'demo' => 'Demo data',
        'worst' => 'Worst case',
        'one' => 'Satu produk',
    ];

    public static function for(string $state): Collection
    {
        return match ($state) {
            'worst' => self::worst(),
            'one' => self::demo()->take(1),
            default => self::demo(),
        };
    }

    public static function demo(): Collection
    {
        return Product::query()
            ->with('category')
            ->withCount(['reviews' => fn (Builder $query) => $query->where('is_approved', true)])
            ->withAvg(['reviews' => fn (Builder $query) => $query->where('is_approved', true)], 'rating')
            ->where('is_active', true)
            ->latest()
            ->take(10)
            ->get();
    }

    public static function worst(): Collection
    {
        $longCategory = 'Aksesoris Konsol, Streaming & Perlengkapan Kreator Konten Profesional';
        $real = self::demo()->first();
        $image = $real?->image_url;

        return collect([
            // Nama di batas 120 karakter + harga maksimum + badge 30 karakter
            self::make(1, [
                'name' => 'Logitech G Pro X Superlight 2 DEX Wireless Gaming Mouse 60g HERO 2 Sensor 44K DPI LIGHTSPEED Edisi Esports Putih Garansi',
                'price' => 9_999_999_999,
                'compare_at_price' => null,
                'badge' => 'Pre-Order Batch 2 Desember 26',
                'image_url' => $image,
            ], 'Mouse Gaming', rating: 4.95, reviews: 1284),

            // Nama pendek, tanpa gambar, kategori 70+ karakter, tepat 1 ulasan
            self::make(2, [
                'name' => 'PS5',
                'price' => 7_999_000,
                'image_url' => null,
            ], $longCategory, rating: 5.0, reviews: 1),

            // Satu "kata" panjang tanpa spasi (kode model resmi)
            self::make(3, [
                'name' => 'ROG-STRIX-SCAR-18-G835LX-SA175W-RTX5090-32GB-2TB-OLED240Hz',
                'price' => 89_999_000,
                'compare_at_price' => 94_999_000,
                'image_url' => $image,
            ], 'Laptop Gaming'),

            // Diskon ekstrem: Rp1 dari Rp150.000, stok habis
            self::make(4, [
                'name' => 'Voucher Flash Sale Keycap Artisan Resin 🐱 Kucing Oren Handmade',
                'price' => 1,
                'compare_at_price' => 150_000,
                'stock' => 0,
                'badge' => 'Flash Sale',
                'image_url' => $image,
            ], 'Keyboard & Keycap', rating: 3.0, reviews: 2),

            // URL gambar yang lolos validasi tapi sudah tidak ada (404)
            self::make(5, [
                'name' => 'HyperX Cloud III Wireless',
                'price' => 0,
                'image_url' => '/images/produk/sudah-dihapus.webp',
            ], 'Headset'),

            // Harga dengan sen dari import CSV + badge berisi emoji
            self::make(6, [
                'name' => 'Hot Wheels Premium Car Culture Nissan Skyline GT-R (BNR34)',
                'price' => 149_999.50,
                'compare_at_price' => 150_000,
                'badge' => '🔥 Terlaris',
                'image_url' => $image,
            ], 'Diecast', rating: 4.04, reviews: 87),
        ]);
    }

    private static function make(int $id, array $attributes, string $category, ?float $rating = null, int $reviews = 0): Product
    {
        $product = new Product(array_merge([
            'slug' => 'dev-break-ui-'.$id,
            'sku' => 'DEV-'.$id,
            'stock' => 12,
            'compare_at_price' => null,
            'badge' => null,
            'is_active' => true,
        ], $attributes));

        $product->id = 900_000 + $id;
        $product->setRelation('category', new Category(['name' => $category, 'slug' => 'dev-'.$id]));
        $product->reviews_count = $reviews;
        $product->reviews_avg_rating = $rating;

        return $product;
    }
}
