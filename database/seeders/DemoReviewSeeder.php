<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Ulasan contoh untuk demo. Hanya menambah/memperbarui ulasan akun contoh dan tidak menghapus apa pun,
 * jadi aman dijalankan berulang: php artisan db:seed --class=DemoReviewSeeder
 */
class DemoReviewSeeder extends Seeder
{
    private const REVIEWS = [
        ['PlayStation 5 Console', 'budi', 5, 'Baru sampai dan langsung dicoba. Loading game cepat banget, DualSense-nya juga enak di tangan. Packing kayu aman.', 2],
        ['HyperX Cloud III Headset', 'sari', 5, 'Empuk dipakai berjam-jam dan suara langkah di game FPS kedengeran jelas. Mic-nya juga bersih pas dipakai Discord.', 3],
        ['Logitech G Pro X Superlight', 'dewi', 5, 'Ringan banget, sensornya presisi. Baterai seminggu lebih masih aman. Worth it buat yang main kompetitif.', 4],
        ['Neo75 Mechanical Keyboard', 'budi', 5, 'Suara ketikannya enak, tidak nyaring. Build aluminium terasa kokoh dan koneksi Bluetooth stabil.', 6],
        ['ASUS ROG Strix XG248Q', 'sari', 4, 'Gerakan halus banget di 240 Hz. Warna oke untuk main game, cuma standnya agak makan tempat di meja kecil.', 7],
        ['Mini GT 1:64 Porsche 911', 'dewi', 5, 'Detailnya rapi untuk ukuran sekecil ini. Catnya mengkilap dan kotaknya sampai tanpa penyok.', 9],
        ['Hot Wheels Nissan Skyline R34', 'budi', 5, 'Kondisi blister mulus, cocok untuk koleksi. Pengirimannya juga dibungkus bubble wrap tebal.', 10],
        ['Monkey D. Luffy Action Figure', 'sari', 5, 'Figurnya gagah, warnanya sesuai foto. Anak saya langsung senang pas dibuka.', 12],
        ['Secretlab Gaming Chair', 'dewi', 4, 'Duduk lama jadi tidak cepat pegal dan bantalannya empuk. Rakitnya butuh dua orang biar gampang.', 14],
        ['MSI GeForce RTX 5060 Ti 16GB', 'budi', 5, 'Main game 1440p setting tinggi lancar, suhunya juga terjaga. Pemasangannya tidak ribet.', 15],
        ['DualSense Wireless Controller', 'dewi', 5, 'Getarannya halus dan triggernya terasa beda dari controller biasa. Baterainya awet untuk beberapa sesi main.', 17],
        ['SSD NVMe 1TB Gen4', 'sari', 5, 'Transfer file besar jadi cepat dan Windows booting sebentar saja. Sudah terpasang tanpa kendala.', 20],
    ];

    public function run(): void
    {
        foreach (self::REVIEWS as [$productName, $username, $rating, $comment, $daysAgo]) {
            $product = Product::query()->where('name', $productName)->first();
            $user = User::query()->where('username', $username)->first();

            if (! $product || ! $user) {
                continue;
            }

            $review = Review::query()->updateOrCreate(
                ['product_id' => $product->id, 'user_id' => $user->id],
                ['rating' => $rating, 'comment' => $comment, 'is_approved' => true]
            );

            $review->forceFill(['created_at' => now()->subDays($daysAgo)])->save();
        }
    }
}
