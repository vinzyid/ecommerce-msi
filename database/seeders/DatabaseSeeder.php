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
            ['name' => 'Keyboard & Mouse', 'slug' => 'keyboard-mouse', 'description' => 'Keyboard mekanikal dan mouse gaming presisi tinggi.'],
            ['name' => 'Audio & Headset', 'slug' => 'audio-headset', 'description' => 'Headset gaming wired dan wireless untuk audio imersif.'],
            ['name' => 'Monitor & Display', 'slug' => 'monitor-display', 'description' => 'Monitor gaming dengan refresh rate tinggi.'],
            ['name' => 'Konsol & Controller', 'slug' => 'konsol-controller', 'description' => 'Konsol generasi terbaru dan controller nirkabel.'],
            ['name' => 'Diecast & Miniatur', 'slug' => 'diecast-miniatur', 'description' => 'Diecast skala 1:64 dan 1:18 untuk kolektor.'],
            ['name' => 'Action Figure', 'slug' => 'action-figure', 'description' => 'Action figure dan figur koleksi karakter favorit.'],
            ['name' => 'Setup & Aksesori', 'slug' => 'setup-aksesori', 'description' => 'Kursi gaming dan pemanis setup kerja.'],
            ['name' => 'Komponen PC', 'slug' => 'komponen-pc', 'description' => 'Graphics card dan memory untuk merakit PC gaming.'],
        ])->mapWithKeys(function (array $data) {
            $category = Category::query()->updateOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);

            return [$data['slug'] => $category];
        });

        return $categories->all();
    }

    private function seedProducts(array $categories): void
    {
        $products = [
            // Keyboard & Mouse
            ['category' => 'keyboard-mouse', 'name' => 'Neo75 Mechanical Keyboard', 'slug' => 'neo75-mechanical-keyboard', 'sku' => 'NAD-KB-001', 'image_url' => '/images/products/keyboard-neo75.jpg', 'description' => 'Keyboard mekanikal custom layout 75% dengan gasket mount, hot-swap socket, dan keycap PBT double-shot. Dilengkapi knob multimedia serta koneksi USB-C dan Bluetooth.', 'price' => 1289000, 'compare_at_price' => 1499000, 'badge' => 'Best Seller', 'weight_grams' => 900, 'material' => 'Aluminium + PBT', 'color' => 'Silver', 'dimensions' => '32 x 14 x 3,5 cm', 'stock' => 18, 'is_featured' => true],
            ['category' => 'keyboard-mouse', 'name' => 'ATK Dragonfly A9 Plus Mouse', 'slug' => 'atk-dragonfly-a9-plus', 'sku' => 'NAD-MS-001', 'image_url' => '/images/products/mouse-atk-dragonfly-a9.jpg', 'description' => 'Mouse gaming nirkabel super ringan dengan sensor optik presisi tinggi, polling rate 1000 Hz, dan switch optik tahan lama. Dilengkapi feet PTFE untuk glide halus.', 'price' => 549000, 'compare_at_price' => 649000, 'badge' => 'Promo', 'weight_grams' => 49, 'material' => 'Plastik ABS', 'color' => 'White', 'dimensions' => '12 x 6,2 x 3,8 cm', 'stock' => 26, 'is_featured' => true],

            // Audio & Headset
            ['category' => 'audio-headset', 'name' => 'HyperX Cloud III Headset', 'slug' => 'hyperx-cloud-iii', 'sku' => 'NAD-HS-001', 'image_url' => '/images/products/headset-hyperx-cloud-3.jpg', 'description' => 'Headset gaming dengan driver 53 mm, earcup memory foam, dan mikrofon noise-cancelling yang bisa dilepas. Nyaman untuk sesi main panjang.', 'price' => 1450000, 'compare_at_price' => 1699000, 'badge' => 'Best Seller', 'weight_grams' => 320, 'material' => 'Aluminium + Memory Foam', 'color' => 'Black', 'dimensions' => '20 x 20 x 10 cm', 'stock' => 14, 'is_featured' => true],
            ['category' => 'audio-headset', 'name' => 'ASUS ROG Pelta Wireless', 'slug' => 'asus-rog-pelta-wireless', 'sku' => 'NAD-HS-002', 'image_url' => '/images/products/headset-asus-rog-pelta.jpg', 'description' => 'Headset nirkabel tri-mode dengan dongle 2.4 GHz, Bluetooth, dan USB-C. Driver 50 mm dengan audio 24-bit dan baterai tahan hingga 90 jam.', 'price' => 2199000, 'compare_at_price' => 2499000, 'badge' => 'Promo', 'weight_grams' => 309, 'material' => 'Aluminium + Kulit Sintetis', 'color' => 'Black', 'dimensions' => '21 x 21 x 11 cm', 'stock' => 9, 'is_featured' => true],

            // Monitor & Display
            ['category' => 'monitor-display', 'name' => 'ASUS ROG Strix XG248Q', 'slug' => 'asus-rog-strix-xg248q', 'sku' => 'NAD-MN-001', 'image_url' => '/images/products/monitor-asus-rog-strix-xg248q.jpg', 'description' => 'Monitor gaming 24 inci Full HD dengan refresh rate 240 Hz dan response time 1 ms. Mendukung FreeSync Premium dan teknologi anti-flicker.', 'price' => 3899000, 'compare_at_price' => 4399000, 'badge' => 'Promo', 'weight_grams' => 6000, 'material' => 'Plastik + Logam', 'color' => 'Black', 'dimensions' => '56 x 22 x 40 cm', 'stock' => 8, 'is_featured' => true],
            ['category' => 'monitor-display', 'name' => 'Xiaomi LED Monitor 24" G24i', 'slug' => 'xiaomi-led-monitor-24-g24i', 'sku' => 'NAD-MN-002', 'image_url' => '/images/products/monitor-xiaomi-g24i.jpg', 'description' => 'Monitor LED 24 inci Full HD dengan panel IPS 165 Hz. Bezel tipis, kaya warna, dan cocok untuk gaming maupun kerja produktif harian.', 'price' => 1549000, 'compare_at_price' => 1799000, 'badge' => 'Promo', 'weight_grams' => 3500, 'material' => 'Plastik', 'color' => 'Black', 'dimensions' => '54 x 18 x 41 cm', 'stock' => 15],

            // Konsol & Controller
            ['category' => 'konsol-controller', 'name' => 'PlayStation 5 Console', 'slug' => 'playstation-5-console', 'sku' => 'NAD-CS-001', 'image_url' => '/images/products/konsol-ps5.jpg', 'description' => 'Konsol generasi terbaru dengan SSD ultra cepat, ray tracing, dan audio 3D. Dilengkapi controller DualSense untuk pengalaman main yang imersif.', 'price' => 7499000, 'compare_at_price' => 8299000, 'badge' => 'Best Seller', 'weight_grams' => 4500, 'material' => 'Plastik', 'color' => 'White', 'dimensions' => '39 x 10 x 26 cm', 'stock' => 6, 'is_featured' => true],
            ['category' => 'konsol-controller', 'name' => 'DualSense Wireless Controller', 'slug' => 'dualsense-wireless-controller', 'sku' => 'NAD-CT-001', 'image_url' => '/images/products/controller-playstation.jpg', 'description' => 'Controller nirkabel dengan haptic feedback dan adaptive trigger. Dilengkapi mikrofon internal serta dukungan Bluetooth untuk PC dan konsol.', 'price' => 899000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 280, 'material' => 'ABS + Silikon', 'color' => 'White', 'dimensions' => '16 x 11 x 6 cm', 'stock' => 24],

            // Diecast & Miniatur
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Toyota AE86', 'slug' => 'minigt-64-toyota-ae86', 'sku' => 'NAD-DC-001', 'image_url' => '/images/products/diecast-minigt-ae86.jpg', 'description' => 'Diecast skala 1:64 dengan bodi logam die-cast dan detail grafis presisi. Ban karet realistis, wajib untuk kolektor model mobil Jepang.', 'price' => 189000, 'compare_at_price' => 229000, 'badge' => 'Promo', 'weight_grams' => 90, 'material' => 'Logam Die-cast', 'color' => 'White Black', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 32, 'is_featured' => true],
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Honda Civic', 'slug' => 'minigt-64-honda-civic', 'sku' => 'NAD-DC-002', 'image_url' => '/images/products/diecast-minigt-civic.jpg', 'description' => 'Diecast skala 1:64 dari logam die-cast dengan cat glossy dan detail interior. Dilengkapi display case transparan untuk pajangan koleksi.', 'price' => 199000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 95, 'material' => 'Logam Die-cast', 'color' => 'Racing Blue', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 28],
            ['category' => 'diecast-miniatur', 'name' => 'Hot Wheels Nissan Skyline R34', 'slug' => 'hot-wheels-nissan-skyline-r34', 'sku' => 'NAD-DC-003', 'image_url' => '/images/products/diecast-hotwheels-r34.jpg', 'description' => 'Diecast Hot Wheels edisi Nissan Skyline GT-R R34 dengan livery ikonik. Bodi logam die-cast dan roda berputar, buruan untuk kolektor.', 'price' => 149000, 'compare_at_price' => 189000, 'badge' => 'Best Seller', 'weight_grams' => 80, 'material' => 'Logam Die-cast', 'color' => 'Silver Blue', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 3, 'is_featured' => true],

            // Action Figure
            ['category' => 'action-figure', 'name' => 'One Piece Action Figure', 'slug' => 'one-piece-action-figure', 'sku' => 'NAD-AF-001', 'image_url' => '/images/products/action-figure-onepiece.jpg', 'description' => 'Action figure karakter One Piece dengan sculpt detail dan pose dinamis. Tinggi sekitar 18 cm, cocok untuk pajangan koleksi maupun hadiah.', 'price' => 359000, 'compare_at_price' => 429000, 'badge' => 'Promo', 'weight_grams' => 320, 'material' => 'PVC + ABS', 'color' => 'Multicolor', 'dimensions' => '18 x 10 x 5 cm', 'stock' => 20, 'is_featured' => true],

            // Setup & Aksesori
            ['category' => 'setup-aksesori', 'name' => 'Secretlab Gaming Chair', 'slug' => 'secretlab-gaming-chair', 'sku' => 'NAD-ST-001', 'image_url' => '/images/products/kursi-secretlab.jpg', 'description' => 'Kursi gaming ergonomis dengan sandaran recline, bantalan lumbar dan leher, serta rangka baja. Bahan kulit PU premium dan roda silent.', 'price' => 4299000, 'compare_at_price' => 4899000, 'badge' => 'Promo', 'weight_grams' => 22000, 'material' => 'Kulit PU + Baja', 'color' => 'Black', 'dimensions' => '70 x 70 x 130 cm', 'stock' => 5, 'is_featured' => true],

            // Komponen PC
            ['category' => 'komponen-pc', 'name' => 'MSI GeForce RTX 5060 Ti 16GB', 'slug' => 'msi-rtx-5060-ti-16gb', 'sku' => 'NAD-PC-001', 'image_url' => '/images/products/gpu-msi-rtx-5060-ti.jpg', 'description' => 'Kartu grafis 16 GB GDDR7 dengan arsitektur terbaru, pendingin kipas ganda, dan dukungan ray tracing. Siap untuk gaming 1440p high refresh rate.', 'price' => 7899000, 'compare_at_price' => 8499000, 'badge' => 'Best Seller', 'weight_grams' => 1200, 'material' => 'Logam + Plastik', 'color' => 'Black', 'dimensions' => '28 x 12 x 4 cm', 'stock' => 7, 'is_featured' => true],
            ['category' => 'komponen-pc', 'name' => 'Colorful GeForce RTX 5060 Ti 16GB', 'slug' => 'colorful-rtx-5060-ti-16gb', 'sku' => 'NAD-PC-002', 'image_url' => '/images/products/gpu-colorful-rtx-5060-ti.jpg', 'description' => 'Kartu grafis 16 GB GDDR7 dengan desain iGame dan sistem pendingin tiga kipas. Mendukung DLSS terbaru untuk performa gaming maksimal.', 'price' => 8199000, 'compare_at_price' => null, 'badge' => 'Editor Pilihan', 'weight_grams' => 1350, 'material' => 'Logam + Plastik', 'color' => 'White', 'dimensions' => '30 x 12 x 5 cm', 'stock' => 5, 'is_featured' => true],
            ['category' => 'komponen-pc', 'name' => 'DDR5 RAM 32GB 6400MHz', 'slug' => 'ddr5-ram-32gb-6400mhz', 'sku' => 'NAD-PC-003', 'image_url' => '/images/products/ram-ddr5-32gb.jpg', 'description' => 'Kit memori DDR5 32 GB (2x16 GB) dengan kecepatan 6400 MHz dan heatsink RGB. Cocok untuk gaming maupun multitasking berat.', 'price' => 2149000, 'compare_at_price' => 2499000, 'badge' => 'Promo', 'weight_grams' => 120, 'material' => 'Aluminium', 'color' => 'RGB Black', 'dimensions' => '13 x 4 x 1 cm', 'stock' => 12],
        ];

        $activeSkus = collect($products)->pluck('sku')->all();

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

        // Nonaktifkan produk lama yang tidak lagi punya gambar/katalog baru.
        Product::query()
            ->whereNotIn('sku', $activeSkus)
            ->update(['is_active' => false]);
    }

    private function seedReviews(): void
    {
        $reviewers = User::query()->where('is_admin', false)->get();
        $products = Product::query()->where('sku', 'like', 'NAD-%')->get();

        if ($reviewers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $comments = [
            5 => ['Kualitas premium, sesuai deskripsi.', 'Packing sangat rapi, pengiriman cepat.', 'Puas banget, pasti beli lagi di sini.', 'Barang original, build quality mantap.'],
            4 => ['Bagus, sesuai harga.', 'Cukup memuaskan, kemasan aman.', 'Sesuai ekspektasi, recommended.', 'Fungsinya oke, pengiriman lancar.'],
            3 => ['Lumayan untuk harganya.', 'Biasa saja, tapi tetap berfungsi.'],
        ];

        foreach ($products as $index => $product) {
            // Variasikan jumlah ulasan agar rating tiap produk berbeda.
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
        Voucher::query()->where('code', 'NADI25')->update(['code' => 'GAMER25']);

        $vouchers = [
            ['code' => 'GAMER10', 'description' => 'Diskon 10% maksimal Rp50.000 untuk semua produk gaming', 'type' => 'percent', 'value' => 10, 'min_spend' => 300000, 'max_discount' => 50000],
            ['code' => 'ONGKIR15', 'description' => 'Potongan Rp15.000 tanpa minimum belanja', 'type' => 'fixed', 'value' => 15000, 'min_spend' => 0, 'max_discount' => null],
            ['code' => 'GAMER25', 'description' => 'Diskon 25% maksimal Rp250.000 untuk belanja di atas Rp1.000.000', 'type' => 'percent', 'value' => 25, 'min_spend' => 1000000, 'max_discount' => 250000],
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
