<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $categories = $this->seedCategories();
        $this->seedProducts($categories);
        $this->seedReviews();
        $this->seedVouchers();
    }

    private function seedUsers(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('Admin123!'),
                'is_admin' => true,
            ]
        );

        foreach (['budi', 'sari', 'dewi'] as $username) {
            User::query()->updateOrCreate(
                ['email' => $username.'@example.com'],
                [
                    'username' => $username,
                    'password' => Hash::make('Password123!'),
                ]
            );
        }
    }

    private function seedCategories(): array
    {
        $categories = collect([
            ['name' => 'Kebutuhan Harian', 'slug' => 'kebutuhan-harian', 'description' => 'Barang yang dipakai setiap hari.'],
            ['name' => 'Rumah', 'slug' => 'rumah', 'description' => 'Peralatan untuk dapur dan ruang tinggal.'],
            ['name' => 'Kerja', 'slug' => 'kerja', 'description' => 'Perlengkapan meja dan aktivitas kerja.'],
            ['name' => 'Hadiah', 'slug' => 'hadiah', 'description' => 'Barang siap diberikan sebagai hadiah.'],
        ])->mapWithKeys(function (array $data) {
            $category = Category::query()->updateOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);

            return [$data['slug'] => $category];
        });

        return $categories->all();
    }

    private function seedProducts(array $categories): void
    {
        $products = [
            // Kebutuhan Harian
            ['category' => 'kebutuhan-harian', 'name' => 'Botol Minum 750 ml', 'slug' => 'botol-minum-750-ml', 'sku' => 'BOT-750-001', 'image_url' => '/images/products/botol-minum.jpg', 'description' => 'Botol minum stainless steel 750 ml dengan desain minimalis dan tahan lama. Cocok untuk aktivitas sehari-hari, olahraga, dan bekerja.', 'price' => 89000, 'compare_at_price' => null, 'badge' => 'Best Seller', 'weight_grams' => 350, 'material' => 'Stainless Steel', 'color' => 'Silver', 'dimensions' => '7 x 7 x 24 cm', 'stock' => 24, 'is_featured' => true],
            ['category' => 'kebutuhan-harian', 'name' => 'Tas Lipat Belanja', 'slug' => 'tas-lipat-belanja', 'sku' => 'BOT-750-002', 'image_url' => '/images/products/tas-lipat.jpg', 'description' => 'Tas belanja kain yang dapat dilipat ke kantong kecil. Beban maksimal 12 kg.', 'price' => 35000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 120, 'material' => 'Kanvas', 'color' => 'Tan', 'dimensions' => '40 x 35 x 10 cm', 'stock' => 40],
            ['category' => 'kebutuhan-harian', 'name' => 'Payung Otomatis', 'slug' => 'payung-otomatis', 'sku' => 'BOT-750-003', 'image_url' => '/images/products/payung.jpg', 'description' => 'Payung delapan rusuk dengan tombol buka otomatis dan pegangan karet.', 'price' => 119000, 'compare_at_price' => 149000, 'badge' => 'Promo', 'weight_grams' => 480, 'material' => 'Poliester', 'color' => 'Hitam', 'dimensions' => '100 cm', 'stock' => 3],

            // Rumah
            ['category' => 'rumah', 'name' => 'Lampu Meja Baca', 'slug' => 'lampu-meja-baca', 'sku' => 'RMH-001', 'image_url' => '/images/products/lampu-meja.jpg', 'description' => 'Lampu meja dengan tiga tingkat terang dan leher yang dapat diatur.', 'price' => 175000, 'compare_at_price' => 199000, 'badge' => 'Promo', 'weight_grams' => 900, 'material' => 'Aluminium', 'color' => 'Putih', 'dimensions' => '18 x 18 x 45 cm', 'stock' => 12, 'is_featured' => true],
            ['category' => 'rumah', 'name' => 'Wadah Bumbu Set 4', 'slug' => 'wadah-bumbu-set-4', 'sku' => 'RMH-002', 'image_url' => '/images/products/wadah-bumbu.jpg', 'description' => 'Empat wadah kaca 250 ml dengan label dan rak besi.', 'price' => 145000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 1200, 'material' => 'Kaca dan Besi', 'color' => 'Bening', 'dimensions' => '30 x 8 x 15 cm', 'stock' => 18],
            ['category' => 'rumah', 'name' => 'Keset Katun 40 x 60', 'slug' => 'keset-katun-40-60', 'sku' => 'RMH-003', 'image_url' => '/images/products/keset.jpg', 'description' => 'Keset tenun katun ukuran 40 x 60 cm. Dapat dicuci dengan mesin.', 'price' => 59000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 600, 'material' => 'Katun', 'color' => 'Abu-abu', 'dimensions' => '40 x 60 cm', 'stock' => 0],

            // Kerja
            ['category' => 'kerja', 'name' => 'Notebook Grid A5', 'slug' => 'notebook-grid-a5', 'sku' => 'KRJ-001', 'image_url' => '/images/products/notebook.jpg', 'description' => 'Buku catatan A5 berisi 160 halaman kertas grid 5 mm.', 'price' => 42000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 300, 'material' => 'Kertas', 'color' => 'Krem', 'dimensions' => '14,8 x 21 cm', 'stock' => 55],
            ['category' => 'kerja', 'name' => 'Dudukan Laptop Aluminium', 'slug' => 'dudukan-laptop-aluminium', 'sku' => 'KRJ-002', 'image_url' => '/images/products/dudukan-laptop.jpg', 'description' => 'Dudukan laptop lipat untuk perangkat 11 sampai 16 inci.', 'price' => 229000, 'compare_at_price' => null, 'badge' => 'Editor Pilihan', 'weight_grams' => 850, 'material' => 'Aluminium', 'color' => 'Silver', 'dimensions' => '26 x 22 x 15 cm', 'stock' => 9, 'is_featured' => true],
            ['category' => 'kerja', 'name' => 'Organizer Kabel Set 6', 'slug' => 'organizer-kabel-set-6', 'sku' => 'KRJ-003', 'image_url' => '/images/products/organizer-kabel.jpg', 'description' => 'Enam penjepit kabel berperekat untuk meja kerja.', 'price' => 27000, 'compare_at_price' => 35000, 'badge' => 'Promo', 'weight_grams' => 90, 'material' => 'Silikon', 'color' => 'Hitam', 'dimensions' => '10 x 2 x 1 cm', 'stock' => 31],

            // Hadiah
            ['category' => 'hadiah', 'name' => 'Lilin Aromaterapi 180 g', 'slug' => 'lilin-aromaterapi-180-g', 'sku' => 'HDH-001', 'image_url' => '/images/products/lilin.jpg', 'description' => 'Lilin kedelai 180 gram dengan aroma kayu cedar. Waktu bakar sekitar 35 jam.', 'price' => 99000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 400, 'material' => 'Soy Wax', 'color' => 'Krem', 'dimensions' => '7 x 7 x 8 cm', 'stock' => 14],
            ['category' => 'hadiah', 'name' => 'Kartu Ucapan Set 8', 'slug' => 'kartu-ucapan-set-8', 'sku' => 'HDH-002', 'image_url' => '/images/products/kartu-ucapan.jpg', 'description' => 'Delapan kartu kosong dan amplop untuk berbagai keperluan.', 'price' => 48000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 180, 'material' => 'Kertas', 'color' => 'Putih', 'dimensions' => '10 x 15 cm', 'stock' => 22],
            ['category' => 'hadiah', 'name' => 'Paket Kopi Drip', 'slug' => 'paket-kopi-drip', 'sku' => 'HDH-003', 'image_url' => '/images/products/kopi.jpg', 'description' => 'Sepuluh kantong kopi drip dengan tiga pilihan biji kopi Indonesia.', 'price' => 125000, 'compare_at_price' => null, 'badge' => 'Best Seller', 'weight_grams' => 250, 'material' => 'Kertas Filter', 'color' => 'Cokelat', 'dimensions' => '12 x 8 x 10 cm', 'stock' => 16, 'is_featured' => true],

            // Tambahan untuk katalog lebih lengkap
            ['category' => 'kerja', 'name' => 'Tanaman Hias Mini', 'slug' => 'tanaman-hias-mini', 'sku' => 'KRJ-004', 'image_url' => null, 'description' => 'Tanaman hias kecil dalam pot keramik untuk menghias meja kerja.', 'price' => 95000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 700, 'material' => 'Keramik', 'color' => 'Hijau', 'dimensions' => '9 x 9 x 18 cm', 'stock' => 20],
            ['category' => 'kebutuhan-harian', 'name' => 'Tumbler Termos 500 ml', 'slug' => 'tumbler-termos-500-ml', 'sku' => 'BOT-750-004', 'image_url' => null, 'description' => 'Tumbler termos 500 ml dengan penutup rapat. Menjaga suhu hingga 12 jam.', 'price' => 135000, 'compare_at_price' => 165000, 'badge' => 'Promo', 'weight_grams' => 420, 'material' => 'Stainless Steel', 'color' => 'Navy', 'dimensions' => '7 x 7 x 20 cm', 'stock' => 27],
            ['category' => 'rumah', 'name' => 'Jam Dinding Minimalis', 'slug' => 'jam-dinding-minimalis', 'sku' => 'RMH-004', 'image_url' => null, 'description' => 'Jam dinding diameter 30 cm dengan angka tipis dan gerakan senyap.', 'price' => 85000, 'compare_at_price' => null, 'badge' => 'Editor Pilihan', 'weight_grams' => 550, 'material' => 'Kayu', 'color' => 'Natural', 'dimensions' => '30 x 30 x 4 cm', 'stock' => 15, 'is_featured' => true],
            ['category' => 'hadiah', 'name' => 'Vas Bunga Keramik', 'slug' => 'vas-bunga-keramik', 'sku' => 'HDH-004', 'image_url' => null, 'description' => 'Vas keramik minimalis untuk bunga kering maupun segar.', 'price' => 110000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 800, 'material' => 'Keramik', 'color' => 'Putih', 'dimensions' => '12 x 12 x 25 cm', 'stock' => 11],
        ];

        foreach ($products as $data) {
            $category = $categories[$data['category']];
            unset($data['category']);

            Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                $data + [
                    'category_id' => $category->id,
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );
        }
    }

    private function seedReviews(): void
    {
        $reviewers = User::query()->where('is_admin', false)->get();
        $products = Product::query()->get();

        if ($reviewers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $comments = [
            5 => ['Kualitas bagus, sesuai deskripsi.', 'Pengiriman cepat, barang rapi.', 'Puas sekali, akan beli lagi.'],
            4 => ['Bagus, sesuai harga.', 'Cukup memuaskan, kemasan aman.', 'Sesuai ekspektasi.'],
            3 => ['Lumayan untuk harganya.', 'Biasa saja, tapi berfungsi.'],
        ];

        foreach ($products as $index => $product) {
            // Variasikan jumlah ulasan agar rating tiap produk berbeda
            $count = 2 + ($index % 4);

            foreach ($reviewers->take($count) as $offset => $user) {
                $rating = [5, 4, 5, 4, 3][($index + $offset) % 5];

                Review::query()->updateOrCreate(
                    ['product_id' => $product->id, 'user_id' => $user->id],
                    [
                        'rating' => $rating,
                        'comment' => $comments[$rating][($index + $offset) % count($comments[$rating])],
                        'is_approved' => true,
                    ]
                );
            }
        }
    }

    private function seedVouchers(): void
    {
        $vouchers = [
            ['code' => 'HEMAT10', 'description' => 'Diskon 10% maksimal Rp20.000', 'type' => 'percent', 'value' => 10, 'min_spend' => 100000, 'max_discount' => 20000],
            ['code' => 'GRATIS15', 'description' => 'Potongan Rp15.000 tanpa minimum', 'type' => 'fixed', 'value' => 15000, 'min_spend' => 0, 'max_discount' => null],
            ['code' => 'NADI25', 'description' => 'Diskon 25% untuk belanja di atas Rp500.000', 'type' => 'percent', 'value' => 25, 'min_spend' => 500000, 'max_discount' => 100000],
        ];

        foreach ($vouchers as $data) {
            Voucher::query()->updateOrCreate(['code' => $data['code']], $data + [
                'usage_limit' => null,
                'used_count' => 0,
                'is_active' => true,
                'expires_at' => now()->addMonths(6),
            ]);
        }
    }
}
