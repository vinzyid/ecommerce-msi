<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('Admin123!'),
                'is_admin' => true,
            ]
        );

        $categories = collect([
            ['name' => 'Kebutuhan Harian', 'slug' => 'kebutuhan-harian', 'description' => 'Barang yang dipakai setiap hari.'],
            ['name' => 'Rumah', 'slug' => 'rumah', 'description' => 'Peralatan untuk dapur dan ruang tinggal.'],
            ['name' => 'Kerja', 'slug' => 'kerja', 'description' => 'Perlengkapan meja dan aktivitas kerja.'],
            ['name' => 'Hadiah', 'slug' => 'hadiah', 'description' => 'Barang siap diberikan sebagai hadiah.'],
        ])->mapWithKeys(function (array $data) {
            $category = Category::query()->updateOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);

            return [$data['slug'] => $category];
        });

        $products = [
            ['category' => 'kebutuhan-harian', 'name' => 'Botol Minum 750 ml', 'slug' => 'botol-minum-750-ml', 'sku' => 'HRN-001', 'image_url' => '/images/products/botol-minum.jpg', 'description' => 'Botol minum berbahan baja tahan karat dengan tutup ulir. Kapasitas 750 ml.', 'price' => 89000, 'stock' => 24, 'is_featured' => true],
            ['category' => 'kebutuhan-harian', 'name' => 'Tas Lipat Belanja', 'slug' => 'tas-lipat-belanja', 'sku' => 'HRN-002', 'image_url' => '/images/products/tas-lipat.jpg', 'description' => 'Tas belanja kain yang dapat dilipat ke kantong kecil. Beban maksimal 12 kg.', 'price' => 35000, 'stock' => 40],
            ['category' => 'kebutuhan-harian', 'name' => 'Payung Otomatis', 'slug' => 'payung-otomatis', 'sku' => 'HRN-003', 'image_url' => '/images/products/payung.jpg', 'description' => 'Payung delapan rusuk dengan tombol buka otomatis dan pegangan karet.', 'price' => 119000, 'stock' => 3],
            ['category' => 'rumah', 'name' => 'Lampu Meja Baca', 'slug' => 'lampu-meja-baca', 'sku' => 'RMH-001', 'image_url' => '/images/products/lampu-meja.jpg', 'description' => 'Lampu meja dengan tiga tingkat terang dan leher yang dapat diatur.', 'price' => 175000, 'stock' => 12, 'is_featured' => true],
            ['category' => 'rumah', 'name' => 'Wadah Bumbu Set 4', 'slug' => 'wadah-bumbu-set-4', 'sku' => 'RMH-002', 'image_url' => '/images/products/wadah-bumbu.jpg', 'description' => 'Empat wadah kaca 250 ml dengan label dan rak besi.', 'price' => 145000, 'stock' => 18],
            ['category' => 'rumah', 'name' => 'Keset Katun 40 × 60', 'slug' => 'keset-katun-40-60', 'sku' => 'RMH-003', 'image_url' => '/images/products/keset.jpg', 'description' => 'Keset tenun katun ukuran 40 × 60 cm. Dapat dicuci dengan mesin.', 'price' => 59000, 'stock' => 0],
            ['category' => 'kerja', 'name' => 'Notebook Grid A5', 'slug' => 'notebook-grid-a5', 'sku' => 'KRJ-001', 'image_url' => '/images/products/notebook.jpg', 'description' => 'Buku catatan A5 berisi 160 halaman kertas grid 5 mm.', 'price' => 42000, 'stock' => 55],
            ['category' => 'kerja', 'name' => 'Dudukan Laptop Aluminium', 'slug' => 'dudukan-laptop-aluminium', 'sku' => 'KRJ-002', 'image_url' => '/images/products/dudukan-laptop.jpg', 'description' => 'Dudukan laptop lipat untuk perangkat 11 sampai 16 inci.', 'price' => 229000, 'stock' => 9, 'is_featured' => true],
            ['category' => 'kerja', 'name' => 'Organizer Kabel Set 6', 'slug' => 'organizer-kabel-set-6', 'sku' => 'KRJ-003', 'image_url' => '/images/products/organizer-kabel.jpg', 'description' => 'Enam penjepit kabel berperekat untuk meja kerja.', 'price' => 27000, 'stock' => 31],
            ['category' => 'hadiah', 'name' => 'Lilin Aromaterapi 180 g', 'slug' => 'lilin-aromaterapi-180-g', 'sku' => 'HDH-001', 'image_url' => '/images/products/lilin.jpg', 'description' => 'Lilin kedelai 180 gram dengan aroma kayu cedar. Waktu bakar sekitar 35 jam.', 'price' => 99000, 'stock' => 14],
            ['category' => 'hadiah', 'name' => 'Kartu Ucapan Set 8', 'slug' => 'kartu-ucapan-set-8', 'sku' => 'HDH-002', 'image_url' => '/images/products/kartu-ucapan.jpg', 'description' => 'Delapan kartu kosong dan amplop untuk berbagai keperluan.', 'price' => 48000, 'stock' => 22],
            ['category' => 'hadiah', 'name' => 'Paket Kopi Drip', 'slug' => 'paket-kopi-drip', 'sku' => 'HDH-003', 'image_url' => '/images/products/kopi.jpg', 'description' => 'Sepuluh kantong kopi drip dengan tiga pilihan biji kopi Indonesia.', 'price' => 125000, 'stock' => 16, 'is_featured' => true],
        ];

        foreach ($products as $data) {
            $category = $categories[$data['category']];
            unset($data['category']);

            Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                $data + [
                    'category_id' => $category->id,
                    'image_url' => null,
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );
        }
    }
}
