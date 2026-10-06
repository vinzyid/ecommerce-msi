<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
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
        $this->seedOrders();
    }

    private function seedUsers(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'haloadmin@vinzyplay.test'],
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
            ['category' => 'keyboard-mouse', 'name' => 'Neo75 Mechanical Keyboard', 'slug' => 'neo75-mechanical-keyboard', 'sku' => 'NAD-KB-001', 'image_url' => '/images/products/keyboard-neo75.webp', 'description' => 'Keyboard mekanikal custom layout 75% dengan gasket mount, hot-swap socket, dan keycap PBT double-shot. Dilengkapi knob multimedia serta koneksi USB-C dan Bluetooth.', 'price' => 1289000, 'compare_at_price' => 1499000, 'badge' => 'Best Seller', 'weight_grams' => 900, 'material' => 'Aluminium + PBT', 'color' => 'Silver', 'dimensions' => '32 x 14 x 3,5 cm', 'stock' => 18, 'is_featured' => true],
            ['category' => 'keyboard-mouse', 'name' => 'Kiboom65 Mechanical Keyboard', 'slug' => 'kiboom65-mechanical-keyboard', 'sku' => 'NAD-KB-002', 'image_url' => '/images/products/keyboard-kiboom65.jpg', 'description' => 'Keyboard mekanikal compact 65% dengan layout minimalis, gasket mount, dan stabilizer pre-lubed. Praktis untuk meja kerja yang rapi dan mobilitas tinggi.', 'price' => 749000, 'compare_at_price' => 899000, 'badge' => 'Promo', 'weight_grams' => 720, 'material' => 'Aluminium + PBT', 'color' => 'Black', 'dimensions' => '30 x 10 x 3 cm', 'stock' => 21],
            ['category' => 'keyboard-mouse', 'name' => 'ATK Dragonfly A9 Plus Mouse', 'slug' => 'atk-dragonfly-a9-plus', 'sku' => 'NAD-MS-001', 'image_url' => '/images/products/mouse-atk-dragonfly-a9.jpg', 'description' => 'Mouse gaming nirkabel super ringan dengan sensor optik presisi tinggi, polling rate 1000 Hz, dan switch optik tahan lama. Dilengkapi feet PTFE untuk glide halus.', 'price' => 549000, 'compare_at_price' => 649000, 'badge' => 'Promo', 'weight_grams' => 49, 'material' => 'Plastik ABS', 'color' => 'White', 'dimensions' => '12 x 6,2 x 3,8 cm', 'stock' => 26, 'is_featured' => true],
            ['category' => 'keyboard-mouse', 'name' => 'Logitech G Pro X Superlight', 'slug' => 'logitech-g-pro-x-superlight', 'sku' => 'NAD-MS-002', 'image_url' => '/images/products/mouse-logitech-gpro-x-superlight.jpg', 'description' => 'Mouse gaming nirkabel ultra ringan 63 gram dengan sensor HERO 25K, koneksi LIGHTSPEED, dan daya tahan baterai hingga 70 jam. Favorit para pro player.', 'price' => 1899000, 'compare_at_price' => 2199000, 'badge' => 'Best Seller', 'weight_grams' => 63, 'material' => 'Plastik ABS', 'color' => 'White', 'dimensions' => '12,5 x 6,3 x 4 cm', 'stock' => 17, 'is_featured' => true],

            // Audio & Headset
            ['category' => 'audio-headset', 'name' => 'HyperX Cloud III Headset', 'slug' => 'hyperx-cloud-iii', 'sku' => 'NAD-HS-001', 'image_url' => '/images/products/headset-hyperx-cloud-3.jpg', 'description' => 'Headset gaming dengan driver 53 mm, earcup memory foam, dan mikrofon noise-cancelling yang bisa dilepas. Nyaman untuk sesi main panjang.', 'price' => 1450000, 'compare_at_price' => 1699000, 'badge' => 'Best Seller', 'weight_grams' => 320, 'material' => 'Aluminium + Memory Foam', 'color' => 'Black', 'dimensions' => '20 x 20 x 10 cm', 'stock' => 14, 'is_featured' => true],
            ['category' => 'audio-headset', 'name' => 'HyperX Cloud Alpha Headset', 'slug' => 'hyperx-cloud-alpha', 'sku' => 'NAD-HS-003', 'image_url' => '/images/products/headset-hyperx-cloud-alpha.jpg', 'description' => 'Headset dengan driver dual-chamber 50 mm yang menghasilkan bass dalam dan treble jernih. Rangka aluminium tahan lama dengan earcup kulit sintetis.', 'price' => 1299000, 'compare_at_price' => 1499000, 'badge' => 'Promo', 'weight_grams' => 298, 'material' => 'Aluminium + Kulit Sintetis', 'color' => 'Black Red', 'dimensions' => '20 x 20 x 10 cm', 'stock' => 16],
            ['category' => 'audio-headset', 'name' => 'ASUS ROG Pelta Wireless', 'slug' => 'asus-rog-pelta-wireless', 'sku' => 'NAD-HS-002', 'image_url' => '/images/products/headset-asus-rog-pelta.jpg', 'description' => 'Headset nirkabel tri-mode dengan dongle 2.4 GHz, Bluetooth, dan USB-C. Driver 50 mm dengan audio 24-bit dan baterai tahan hingga 90 jam.', 'price' => 2199000, 'compare_at_price' => 2499000, 'badge' => 'Promo', 'weight_grams' => 309, 'material' => 'Aluminium + Kulit Sintetis', 'color' => 'Black', 'dimensions' => '21 x 21 x 11 cm', 'stock' => 9, 'is_featured' => true],

            // Monitor & Display
            ['category' => 'monitor-display', 'name' => 'ASUS ROG Strix XG248Q', 'slug' => 'asus-rog-strix-xg248q', 'sku' => 'NAD-MN-001', 'image_url' => '/images/products/monitor-asus-rog-strix-xg248q.jpg', 'description' => 'Monitor gaming 24 inci Full HD dengan refresh rate 240 Hz dan response time 1 ms. Mendukung FreeSync Premium dan teknologi anti-flicker.', 'price' => 3899000, 'compare_at_price' => 4399000, 'badge' => 'Promo', 'weight_grams' => 6000, 'material' => 'Plastik + Logam', 'color' => 'Black', 'dimensions' => '56 x 22 x 40 cm', 'stock' => 8, 'is_featured' => true],
            ['category' => 'monitor-display', 'name' => 'ASUS ROG Strix XG259QNS', 'slug' => 'asus-rog-strix-xg259qns', 'sku' => 'NAD-MN-003', 'image_url' => '/images/products/monitor-asus-rog-strix-xg259qns.webp', 'description' => 'Monitor gaming 25 inci Fast IPS dengan refresh rate 380 Hz dan response time 1 ms. Ideal untuk esports kompetitif dengan akurasi warna tinggi.', 'price' => 4599000, 'compare_at_price' => 4999000, 'badge' => 'Best Seller', 'weight_grams' => 5500, 'material' => 'Plastik + Logam', 'color' => 'Black', 'dimensions' => '56 x 22 x 41 cm', 'stock' => 6, 'is_featured' => true],
            ['category' => 'monitor-display', 'name' => 'Xiaomi LED Monitor 24" G24i 144Hz', 'slug' => 'xiaomi-led-monitor-24-g24i', 'sku' => 'NAD-MN-002', 'image_url' => '/images/products/monitor-xiaomi-g24i-144hz.jpg', 'description' => 'Monitor LED 24 inci Full HD dengan panel IPS 144 Hz. Bezel tipis, kaya warna, dan cocok untuk gaming maupun kerja produktif harian.', 'price' => 1549000, 'compare_at_price' => 1799000, 'badge' => 'Promo', 'weight_grams' => 3500, 'material' => 'Plastik', 'color' => 'Black', 'dimensions' => '54 x 18 x 41 cm', 'stock' => 15],
            ['category' => 'monitor-display', 'name' => 'Samsung Odyssey G3 24"', 'slug' => 'samsung-odyssey-g3-24', 'sku' => 'NAD-MN-004', 'image_url' => '/images/products/monitor-samsung-odyssey-g3.jpg', 'description' => 'Monitor gaming 24 inci dengan refresh rate 165 Hz dan response time 1 ms. Panel VA dengan kontras tinggi serta dukungan AMD FreeSync Premium.', 'price' => 1899000, 'compare_at_price' => 2199000, 'badge' => 'Promo', 'weight_grams' => 4300, 'material' => 'Plastik', 'color' => 'Black', 'dimensions' => '55 x 21 x 42 cm', 'stock' => 11],
            ['category' => 'monitor-display', 'name' => 'Skyworth F27G67Q 27"', 'slug' => 'skyworth-f27g67q-27', 'sku' => 'NAD-MN-005', 'image_url' => '/images/products/monitor-skyworth-f27g67q.jpg', 'description' => 'Monitor gaming 27 inci QHD dengan refresh rate 180 Hz dan panel Fast IPS. Menghadirkan visual tajam untuk gaming dan produktivitas.', 'price' => 2799000, 'compare_at_price' => 3199000, 'badge' => null, 'weight_grams' => 5000, 'material' => 'Plastik', 'color' => 'Black', 'dimensions' => '61 x 22 x 45 cm', 'stock' => 9],

            // Konsol & Controller
            ['category' => 'konsol-controller', 'name' => 'PlayStation 5 Console', 'slug' => 'playstation-5-console', 'sku' => 'NAD-CS-001', 'image_url' => '/images/products/konsol-ps5.jpg', 'description' => 'Konsol generasi terbaru dengan SSD ultra cepat, ray tracing, dan audio 3D. Dilengkapi controller DualSense untuk pengalaman main yang imersif.', 'price' => 7499000, 'compare_at_price' => 8299000, 'badge' => 'Best Seller', 'weight_grams' => 4500, 'material' => 'Plastik', 'color' => 'White', 'dimensions' => '39 x 10 x 26 cm', 'stock' => 6, 'is_featured' => true],
            ['category' => 'konsol-controller', 'name' => 'PlayStation 4 Slim Console', 'slug' => 'playstation-4-slim-console', 'sku' => 'NAD-CS-002', 'image_url' => '/images/products/konsol-ps4.jpg', 'description' => 'Konsol PS4 Slim dengan desain ringkas dan hemat daya. Mendukung koleksi game PS4 yang luas serta layanan PlayStation Plus.', 'price' => 3299000, 'compare_at_price' => 3799000, 'badge' => 'Promo', 'weight_grams' => 2100, 'material' => 'Plastik', 'color' => 'Black', 'dimensions' => '29 x 26 x 4 cm', 'stock' => 8],
            ['category' => 'konsol-controller', 'name' => 'DualSense Wireless Controller', 'slug' => 'dualsense-wireless-controller', 'sku' => 'NAD-CT-001', 'image_url' => '/images/products/controller-playstation.jpg', 'description' => 'Controller nirkabel dengan haptic feedback dan adaptive trigger. Dilengkapi mikrofon internal serta dukungan Bluetooth untuk PC dan konsol.', 'price' => 899000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 280, 'material' => 'ABS + Silikon', 'color' => 'White', 'dimensions' => '16 x 11 x 6 cm', 'stock' => 24],
            ['category' => 'konsol-controller', 'name' => 'Fantech Gamepad Controller', 'slug' => 'fantech-gamepad-controller', 'sku' => 'NAD-CT-002', 'image_url' => '/images/products/controller-fantech-gamepad.webp', 'description' => 'Gamepad nirkabel dengan konektivitas Bluetooth dan USB-C, tombol responsif, serta dukungan platform PC, Android, dan Switch. Nyaman untuk sesi main panjang.', 'price' => 379000, 'compare_at_price' => 449000, 'badge' => 'Promo', 'weight_grams' => 260, 'material' => 'ABS + Silikon', 'color' => 'White', 'dimensions' => '16 x 11 x 6 cm', 'stock' => 22],

            // Diecast & Miniatur
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Toyota AE86', 'slug' => 'minigt-64-toyota-ae86', 'sku' => 'NAD-DC-001', 'image_url' => '/images/products/diecast-minigt-ae86.jpg', 'description' => 'Diecast skala 1:64 dengan bodi logam die-cast dan detail grafis presisi. Ban karet realistis, wajib untuk kolektor model mobil Jepang.', 'price' => 189000, 'compare_at_price' => 229000, 'badge' => 'Promo', 'weight_grams' => 90, 'material' => 'Logam Die-cast', 'color' => 'White Black', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 32, 'is_featured' => true],
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Honda Civic', 'slug' => 'minigt-64-honda-civic', 'sku' => 'NAD-DC-002', 'image_url' => '/images/products/diecast-minigt-civic.jpg', 'description' => 'Diecast skala 1:64 dari logam die-cast dengan cat glossy dan detail interior. Dilengkapi display case transparan untuk pajangan koleksi.', 'price' => 199000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 95, 'material' => 'Logam Die-cast', 'color' => 'Racing Blue', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 28],
            ['category' => 'diecast-miniatur', 'name' => 'Hot Wheels Nissan Skyline R34', 'slug' => 'hot-wheels-nissan-skyline-r34', 'sku' => 'NAD-DC-003', 'image_url' => '/images/products/diecast-hotwheels-r34.jpg', 'description' => 'Diecast Hot Wheels edisi Nissan Skyline GT-R R34 dengan livery ikonik. Bodi logam die-cast dan roda berputar, buruan untuk kolektor.', 'price' => 149000, 'compare_at_price' => 189000, 'badge' => 'Best Seller', 'weight_grams' => 80, 'material' => 'Logam Die-cast', 'color' => 'Silver Blue', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 3, 'is_featured' => true],
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Nissan ER34 LBWK', 'slug' => 'minigt-64-nissan-er34-lbwk', 'sku' => 'NAD-DC-004', 'image_url' => '/images/products/diecast-minigt-er34-lbwk.jpg', 'description' => 'Diecast Mini GT skala 1:64 edisi Nissan ER34 Liberty Walk. Bodi widebody dengan detail grafis tajam dan velg racing yang presisi.', 'price' => 219000, 'compare_at_price' => 259000, 'badge' => 'Promo', 'weight_grams' => 92, 'material' => 'Logam Die-cast', 'color' => 'White', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 19],
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Porsche 911', 'slug' => 'minigt-64-porsche-911', 'sku' => 'NAD-DC-005', 'image_url' => '/images/products/diecast-minigt-porsche-911.jpg', 'description' => 'Diecast Mini GT skala 1:64 Porsche 911 dengan bodi ikonik dan cat glossy. Detail lampu serta interior yang tajam, cocok untuk koleksi premium.', 'price' => 209000, 'compare_at_price' => null, 'badge' => null, 'weight_grams' => 90, 'material' => 'Logam Die-cast', 'color' => 'Yellow', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 24],
            ['category' => 'diecast-miniatur', 'name' => 'Mini GT 1:64 Nissan R35 White', 'slug' => 'minigt-64-nissan-r35-white', 'sku' => 'NAD-DC-006', 'image_url' => '/images/products/diecast-minigt-r35-white.jpg', 'description' => 'Diecast Mini GT skala 1:64 Nissan GT-R R35 warna putih. Bodi logam die-cast dengan detail grille dan velg yang rapi untuk display koleksi.', 'price' => 199000, 'compare_at_price' => 239000, 'badge' => 'Promo', 'weight_grams' => 90, 'material' => 'Logam Die-cast', 'color' => 'White', 'dimensions' => '7 x 3 x 3 cm', 'stock' => 27],

            // Action Figure
            ['category' => 'action-figure', 'name' => 'One Piece Action Figure', 'slug' => 'one-piece-action-figure', 'sku' => 'NAD-AF-001', 'image_url' => '/images/products/action-figure-onepiece.webp', 'description' => 'Action figure karakter One Piece dengan sculpt detail dan pose dinamis. Tinggi sekitar 18 cm, cocok untuk pajangan koleksi maupun hadiah.', 'price' => 359000, 'compare_at_price' => 429000, 'badge' => 'Promo', 'weight_grams' => 320, 'material' => 'PVC + ABS', 'color' => 'Multicolor', 'dimensions' => '18 x 10 x 5 cm', 'stock' => 20, 'is_featured' => true],
            ['category' => 'action-figure', 'name' => 'Monkey D. Luffy Action Figure', 'slug' => 'monkey-d-luffy-action-figure', 'sku' => 'NAD-AF-002', 'image_url' => '/images/products/action-figure-luffy.jpg', 'description' => 'Action figure Monkey D. Luffy dari One Piece dengan detail sculpt tajam dan pose ikonik. Tinggi sekitar 20 cm, wajib untuk penggemar dan kolektor.', 'price' => 379000, 'compare_at_price' => 449000, 'badge' => 'Best Seller', 'weight_grams' => 350, 'material' => 'PVC + ABS', 'color' => 'Multicolor', 'dimensions' => '20 x 11 x 6 cm', 'stock' => 18, 'is_featured' => true],

            // Setup & Aksesori
            ['category' => 'setup-aksesori', 'name' => 'Secretlab Gaming Chair', 'slug' => 'secretlab-gaming-chair', 'sku' => 'NAD-ST-001', 'image_url' => '/images/products/kursi-secretlab.jpg', 'description' => 'Kursi gaming ergonomis dengan sandaran recline, bantalan lumbar dan leher, serta rangka baja. Bahan kulit PU premium dan roda silent.', 'price' => 4299000, 'compare_at_price' => 4899000, 'badge' => 'Promo', 'weight_grams' => 22000, 'material' => 'Kulit PU + Baja', 'color' => 'Black', 'dimensions' => '70 x 70 x 130 cm', 'stock' => 5, 'is_featured' => true],

            // Komponen PC
            ['category' => 'komponen-pc', 'name' => 'MSI GeForce RTX 5060 Ti 16GB', 'slug' => 'msi-rtx-5060-ti-16gb', 'sku' => 'NAD-PC-001', 'image_url' => '/images/products/gpu-msi-rtx-5060-ti.jpg', 'description' => 'Kartu grafis 16 GB GDDR7 dengan arsitektur terbaru, pendingin kipas ganda, dan dukungan ray tracing. Siap untuk gaming 1440p high refresh rate.', 'price' => 7899000, 'compare_at_price' => 8499000, 'badge' => 'Best Seller', 'weight_grams' => 1200, 'material' => 'Logam + Plastik', 'color' => 'Black', 'dimensions' => '28 x 12 x 4 cm', 'stock' => 7, 'is_featured' => true],
            ['category' => 'komponen-pc', 'name' => 'Colorful GeForce RTX 5060 Ti 16GB', 'slug' => 'colorful-rtx-5060-ti-16gb', 'sku' => 'NAD-PC-002', 'image_url' => '/images/products/gpu-colorful-rtx-5060-ti.jpg', 'description' => 'Kartu grafis 16 GB GDDR7 dengan desain iGame dan sistem pendingin tiga kipas. Mendukung DLSS terbaru untuk performa gaming maksimal.', 'price' => 8199000, 'compare_at_price' => null, 'badge' => 'Editor Pilihan', 'weight_grams' => 1350, 'material' => 'Logam + Plastik', 'color' => 'White', 'dimensions' => '30 x 12 x 5 cm', 'stock' => 5, 'is_featured' => true],
            ['category' => 'komponen-pc', 'name' => 'Zotac GeForce RTX 5060 Ti 16GB', 'slug' => 'zotac-rtx-5060-ti-16gb', 'sku' => 'NAD-PC-005', 'image_url' => '/images/products/gpu-zotac-rtx-5060-ti.jpg', 'description' => 'Kartu grafis 16 GB GDDR7 dengan desain kompak dan pendingin IceStorm. Efisien untuk gaming 1440p dengan konsumsi daya terkendali.', 'price' => 7699000, 'compare_at_price' => 8199000, 'badge' => 'Promo', 'weight_grams' => 1150, 'material' => 'Logam + Plastik', 'color' => 'Black', 'dimensions' => '23 x 12 x 4 cm', 'stock' => 6],
            ['category' => 'komponen-pc', 'name' => 'Gigabyte GeForce RTX 5090 32GB', 'slug' => 'gigabyte-rtx-5090-32gb', 'sku' => 'NAD-PC-006', 'image_url' => '/images/products/gpu-gigabyte-rtx-5090.jpg', 'description' => 'Kartu grafis flagship 32 GB GDDR7 dengan arsitektur terbaru. Pendingin Windforce tiga kipas untuk performa gaming 4K dan rendering berat.', 'price' => 32990000, 'compare_at_price' => 35990000, 'badge' => 'Best Seller', 'weight_grams' => 2400, 'material' => 'Logam + Plastik', 'color' => 'Black', 'dimensions' => '34 x 14 x 7 cm', 'stock' => 3, 'is_featured' => true],
            ['category' => 'komponen-pc', 'name' => 'DDR5 RAM 32GB 6400MHz', 'slug' => 'ddr5-ram-32gb-6400mhz', 'sku' => 'NAD-PC-003', 'image_url' => '/images/products/ram-ddr5-32gb.jpg', 'description' => 'Kit memori DDR5 32 GB (2x16 GB) dengan kecepatan 6400 MHz dan heatsink RGB. Cocok untuk gaming maupun multitasking berat.', 'price' => 2149000, 'compare_at_price' => 2499000, 'badge' => 'Promo', 'weight_grams' => 120, 'material' => 'Aluminium', 'color' => 'RGB Black', 'dimensions' => '13 x 4 x 1 cm', 'stock' => 12],
            ['category' => 'komponen-pc', 'name' => 'Corsair DDR5 RAM 32GB 6000MHz', 'slug' => 'corsair-ddr5-ram-32gb-6000mhz', 'sku' => 'NAD-PC-007', 'image_url' => '/images/products/ram-ddr5-32gb-corsair.jpg', 'description' => 'Kit memori Corsair DDR5 32 GB (2x16 GB) 6000 MHz dengan heatsink aluminium dan dukungan RGB. Stabil untuk gaming dan penciptaan konten.', 'price' => 2299000, 'compare_at_price' => 2699000, 'badge' => 'Editor Pilihan', 'weight_grams' => 130, 'material' => 'Aluminium', 'color' => 'RGB Black', 'dimensions' => '13 x 4 x 1 cm', 'stock' => 10],
            ['category' => 'komponen-pc', 'name' => 'Intel Core Ultra 7 265KF', 'slug' => 'intel-core-ultra-7-265kf', 'sku' => 'NAD-PC-008', 'image_url' => '/images/products/cpu-intel-core-ultra-7-265kf.webp', 'description' => 'Prosesor Intel Core Ultra 7 265KF dengan 20 core dan arsitektur terbaru. Performa tinggi untuk gaming, streaming, dan produktivitas berat.', 'price' => 6299000, 'compare_at_price' => 6799000, 'badge' => 'Promo', 'weight_grams' => 120, 'material' => 'Silikon + Logam', 'color' => 'Silver', 'dimensions' => '4,5 x 4,5 x 1 cm', 'stock' => 7, 'is_featured' => true],
            ['category' => 'komponen-pc', 'name' => 'SSD NVMe 1TB Gen4', 'slug' => 'ssd-nvme-1tb-gen4', 'sku' => 'NAD-PC-009', 'image_url' => '/images/products/ssd-1tb.jpg', 'description' => 'SSD NVMe PCIe Gen4 dengan kapasitas 1 TB dan kecepatan baca hingga 7000 MB/s. Mempercepat booting, loading game, dan transfer file besar.', 'price' => 1099000, 'compare_at_price' => 1299000, 'badge' => 'Promo', 'weight_grams' => 40, 'material' => 'Aluminium', 'color' => 'Black', 'dimensions' => '8 x 2,2 x 0,3 cm', 'stock' => 25],
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

    private function seedOrders(): void
    {
        // Hapus pesanan lama agar data dummy selalu konsisten dengan katalog terbaru.
        OrderItem::query()->delete();
        Order::query()->delete();

        $customers = User::query()->where('is_admin', false)->get();
        $products = Product::query()->where('sku', 'like', 'NAD-%')->where('is_active', true)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $locations = [
            ['province' => 'DKI Jakarta', 'city' => 'Jakarta Selatan', 'district' => 'Kebayoran Baru', 'postal_code' => '12110'],
            ['province' => 'Jawa Barat', 'city' => 'Bandung', 'district' => 'Coblong', 'postal_code' => '40132'],
            ['province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Gubeng', 'postal_code' => '60281'],
            ['province' => 'DI Yogyakarta', 'city' => 'Yogyakarta', 'district' => 'Depok', 'postal_code' => '55281'],
            ['province' => 'Banten', 'city' => 'Tangerang', 'district' => 'Ciledug', 'postal_code' => '15151'],
        ];

        $statuses = ['pending', 'processing', 'shipped', 'completed', 'completed', 'cancelled'];
        $payments = ['transfer', 'cod'];
        $shippings = ['regular' => 15000, 'express' => 25000];

        // Buat 42 pesanan.
        for ($i = 0; $i < 42; $i++) {
            $customer = $customers[$i % $customers->count()];
            $location = $locations[$i % count($locations)];
            $status = $statuses[$i % count($statuses)];
            $payment = $payments[$i % count($payments)];
            $shippingKey = $i % 3 === 0 ? 'express' : 'regular';
            $shippingCost = $shippings[$shippingKey];
            $orderedAt = now()->subDays(42 - $i)->setTime(8 + ($i % 10), ($i * 7) % 60);

            // 1-3 produk per pesanan.
            $itemCount = 1 + ($i % 3);
            $chosen = $products->shuffle()->take($itemCount);

            $subtotal = 0;
            $items = [];
            foreach ($chosen as $product) {
                $qty = 1 + (($i + $product->id) % 2);
                $lineTotal = (int) $product->price * $qty;
                $subtotal += $lineTotal;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $lineTotal,
                ];
            }

            $order = Order::query()->create([
                'order_number' => 'NAD-'.now()->format('ymd').'-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'user_id' => $customer->id,
                'customer_name' => $customer->username,
                'phone' => '08'.str_pad((string) (1200000000 + $i * 1234567), 10, '0', STR_PAD_LEFT),
                'address' => 'Jl. Merdeka No. '.($i + 1),
                'province' => $location['province'],
                'city' => $location['city'],
                'district' => $location['district'],
                'postal_code' => $location['postal_code'],
                'shipping_method' => $shippingKey,
                'payment_method' => $payment,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
                'status' => $status,
                'ordered_at' => $orderedAt,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
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
