<div align="center">

# 🎮 VinzyPlay — E-Commerce Marketplace

**Platform belanja daring untuk Gaming Gears, Diecast Koleksi, Komponen PC & Hobi**

Dibangun dengan Laravel 12 · Tailwind CSS v4 · PostgreSQL (Neon) · AI Customer Assistant

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)

</div>

---

## 📖 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Unggulan](#-fitur-unggulan)
- [Tumpukan Teknologi](#-tumpukan-teknologi)
- [Arsitektur & Struktur Data](#-arsitektur--struktur-data)
- [Instalasi](#-instalasi)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Akun Development](#-akun-development)
- [Pengujian](#-pengujian)
- [Struktur Proyek](#-struktur-proyek)
- [Dokumentasi Tambahan](#-dokumentasi-tambahan)

---

## 🎯 Tentang Proyek

**VinzyPlay** adalah aplikasi e-commerce bertema marketplace (mirip Tokopedia & Shopee) yang dikhususkan untuk
perlengkapan **gaming**, **diecast koleksi**, **komponen PC**, dan **hobi**. Proyek ini merupakan hasil redesain
total antarmuka sekaligus penambahan puluhan fitur modern — mulai dari pelacakan pengiriman, buku alamat otomatis,
hingga asisten belanja berbasis AI dengan konsep *Green Computing*.

> **Nama toko bersifat konfigurabel.** Nama diambil dari `APP_NAME` di `.env` dan otomatis dipakai pada judul
> halaman, logo, footer, serta sapaan chatbot.
>
> ```dotenv
> APP_NAME="VinzyPlay"
> ```

**Developer:** Rafi Pandya P

---

## ✨ Fitur Unggulan

### 🛍️ Storefront Modern (Gaya Tokopedia & Shopee)
- **Top Utility Bar** — unduh aplikasi, mitra seller, info gratis ongkir, CS 24/7
- **Trending Search Bar** — pencarian *case-insensitive* (`ILIKE`), multi-kata, lintas kategori & SKU,
  dilengkapi tag pencarian populer yang bisa diklik
- **Hero 3-Slot Banner** — slider carousel promosi (auto-slide + navigasi) dengan kartu promo samping
- **Klaim Voucher Toko** — salin kode kupon dengan satu klik + notifikasi pop-up
- **Flash Sale & Kejar Diskon** — countdown timer real-time + progress bar penjualan
- **Official Brands Pavilion** — PlayStation, ASUS ROG, Logitech G, HyperX, Secretlab, Mini GT, MSI, Hot Wheels
- **Product Card Rich** — badge Official Store, Bebas Ongkir, lokasi, rating, jumlah terjual
- **Bottom Navigation Mobile** — navigasi cepat ala aplikasi mobile di layar kecil

### 🏷️ Detail Produk
- Galeri produk, status diskon, dan jaminan 100% Original
- **Store Card** — profil toko resmi + tombol *Chat Penjual* yang langsung membuka chatbot
- Estimasi pengiriman, pilihan kurir, simulasi cicilan, dan metode pembayaran
- **Sticky Mobile Buy Bar** — tombol beli tetap terlihat saat menggulir
- **Ulasan Pembeli Terverifikasi** — hanya pengguna yang benar-benar membeli produk yang dapat mengulas

### 🛒 Keranjang Belanja Pintar
- **Pilih barang mau dibeli** — centang item individual atau "Pilih Semua" (*partial checkout*)
- **Kalkulasi real-time** — subtotal, jumlah barang, dan total dihitung otomatis tanpa reload
- **Progress Bar Bebas Ongkir** — menampilkan sisa belanjaan hingga gratis ongkir
- Stepper kuantitas interaktif `[-]` `[+]`
- **Beli Sekarang** — langsung checkout untuk produk tunggal

### 💳 Checkout & Pembayaran
- Alur 3 tahap: **Alamat → Pengiriman → Pembayaran**
- Pilihan kurir: **Reguler** (bebas ongkir min. Rp300.000) & **Kilat** (Rp25.000)
- Metode pembayaran: **Transfer Bank / Virtual Account** (BCA, Mandiri, BRI, BNI) & **COD**
- **Buku Alamat Otomatis** — alamat tersimpan otomatis, checkout berikutnya langsung terisi
- Checkout sebagian — hanya barang yang dipilih yang dihapus dari keranjang

### 🚚 Pelacakan Pesanan
- **Stepper status**: Menunggu Bayar → Diproses → Dikirim → Selesai
- **Simulasi pembayaran sandbox** — tandai lunas tanpa uang asli untuk keperluan demonstrasi
- **Penerbitan resi otomatis** + pilihan kurir
- **Timeline pelacakan kurir** — log perjalanan paket bertahap (khas Shopee/Tokopedia)
- Konfirmasi pesanan diterima yang membuka akses ulasan

### 🤖 Chatbot Asisten AI
- Terintegrasi API LLM kompatibel OpenAI (Grok/OpenAI/OpenRouter/DeepSeek)
- **Memori percakapan** — mengirim 8 pesan terakhir agar konteks tidak hilang
- **Guardrail anti-offtopic** — hanya menjawab seputar belanja toko
- **Format ramah ponsel** — tanpa tabel markdown rusak, perbandingan produk terstruktur
- **Green Computing** — cache pertanyaan mirip untuk menghemat token LLM, listrik (kWh), dan emisi karbon (CO₂e)

### 👤 Kelola Akun
- Dashboard profil: total pesanan, pesanan aktif, total belanja, wishlist
- Tab interaktif: **Profil**, **Alamat Saya**, **Keamanan**, **Riwayat Pesanan**
- Buku alamat (tambah, hapus, jadikan alamat utama)
- Ganti password terenkripsi dengan tombol lihat password (ikon mata)

### 🔔 Pengalaman Pengguna
- **Notifikasi toast pop-up** dengan efek *glassmorphism*, progress bar, dan auto-dismiss
- **Efek suara "centung"** (Web Audio API) saat aksi berhasil
- Riwayat chatbot terisolasi per akun & otomatis dibersihkan saat logout

### 🛠️ Panel Admin
- Dashboard ringkasan & grafik (produk per kategori, pendapatan bulanan, status pesanan)
- Pengelolaan kategori, produk, stok, harga, promo, label, dan foto
- Pengelolaan pengguna, peran akun, dan voucher
- Daftar & detail pesanan + perubahan status
- **Dashboard Chatbot**: statistik cache, hit rate, dan metrik Green Computing

---

## 🧰 Tumpukan Teknologi

| Kategori | Teknologi |
|---|---|
| **Backend** | PHP 8.4 · Laravel 12 |
| **Frontend** | Blade Templating · Tailwind CSS v4 · Vanilla JavaScript |
| **Build Tool** | Vite 7 + Laravel Vite Plugin |
| **Database** | PostgreSQL 16 pada Neon Serverless Cloud |
| **AI** | LLM kompatibel OpenAI (chat completions) |
| **Testing** | PHPUnit |

---

## 🗄️ Arsitektur & Struktur Data

Aplikasi menggunakan **13 tabel inti** dengan relasi foreign key yang terjaga:

```
users ──┬── user_addresses        (buku alamat, ON DELETE CASCADE)
        ├── cart_items            (keranjang, CASCADE)
        ├── orders                (pesanan, RESTRICT)
        ├── reviews               (ulasan, CASCADE)
        ├── wishlists             (favorit, CASCADE)
        └── chatbot_messages      (cache AI, SET NULL)

categories ── products ──┬── cart_items   (CASCADE)
                         ├── order_items  (SET NULL — snapshot historis)
                         ├── reviews      (CASCADE)
                         └── wishlists    (CASCADE)

orders ── order_items      (rincian, CASCADE)
vouchers ── orders         (diskon, SET NULL)
```

📊 **Diagram ERD lengkap, alur transaksi, dan flow chatbot tersedia di → [`FLOW_DATABASE.md`](FLOW_DATABASE.md)**

---

## ⚙️ Instalasi

### Prasyarat
- PHP **8.2+** (disarankan 8.4)
- Composer 2
- Node.js & npm
- PostgreSQL (lokal atau [Neon](https://neon.tech) gratis)

### Langkah-langkah

**1. Clone repository**

```bash
git clone https://github.com/vinzyid/ecommerce-msi.git
cd ecommerce
```

**2. Pasang dependency**

```bash
composer install
npm install
```

**3. Siapkan environment**

```bash
cp .env.example .env
php artisan key:generate
```

**4. Konfigurasi database**

Untuk **PostgreSQL lokal / Neon**, atur di `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=ep-xxxxx.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=username
DB_PASSWORD=password
DB_SSLMODE=require
DB_PERSISTENT=false
```

> ⚠️ **Penting untuk Neon:** gunakan **host direct** (tanpa `-pooler`). Host pooler memakai PgBouncer yang
> dapat menyebabkan error `SQLSTATE[25P02]` pada transaksi checkout.

**5. Migrasi & data contoh**

```bash
php artisan migrate --seed
```

**6. Bangun aset frontend**

```bash
npm run build
```

---

## 🚀 Menjalankan Aplikasi

```bash
php artisan serve
```

Buka **http://127.0.0.1:8000** di browser.

Untuk pengembangan dengan *hot reload*:

```bash
npm run dev
```

> 💡 **Tips:** pastikan hanya ada **satu** proses `php artisan serve` yang berjalan. Jika ada proses menggantung,
> hentikan dengan `Ctrl + C` atau jalankan `Get-Process php | Stop-Process -Force` (Windows).

---

## 🔑 Akun Development

| Peran | Email | Password |
|---|---|---|
| **Admin** | `haloadmin@vinzyplay.test` | *(lihat seeder)* |
| **Pelanggan** | `budi@example.com` | `Password123!` |

Panel admin tersedia di **http://127.0.0.1:8000/admin**.

> 🔒 Akun di atas hanya untuk development. **Ganti password sebelum deploy ke server publik.**

---

## 🧪 Pengujian

```bash
php artisan test
```

---

## 📁 Struktur Proyek

```text
ecommerce/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Controller panel admin
│   │   │   ├── Auth/           # Login & register
│   │   │   ├── CartController.php
│   │   │   ├── CatalogController.php
│   │   │   ├── ChatbotController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── OrderController.php
│   │   │   └── ReviewController.php
│   │   └── Middleware/
│   ├── Models/                 # Eloquent: Product, Order, UserAddress, dll
│   ├── Services/               # ChatbotService (integrasi AI)
│   └── Support/                # CartCalculator (logika keranjang)
│
├── database/
│   ├── migrations/             # 10 file migrasi skema
│   └── seeders/
│
├── resources/
│   ├── css/app.css             # Tema Tailwind (brand, ink, accent)
│   ├── js/app.js               # Toast, chatbot, carousel, password toggle
│   └── views/
│       ├── account/            # Profil, alamat, keamanan
│       ├── admin/              # Panel admin & dashboard chatbot
│       ├── auth/
│       ├── cart/
│       ├── catalog/            # Beranda, detail produk, promo
│       ├── checkout/
│       ├── components/         # Product card, stars, badge
│       ├── layouts/
│       ├── orders/             # Detail + timeline pelacakan
│       ├── partials/           # Chatbot widget, icons
│       └── wishlist/
│
├── public/
│   └── images/products/        # Foto produk
│
├── FLOW_DATABASE.md            # 📊 Diagram database & alur data
├── PRESENTASI_PROYEK.md        # 📽️ Materi presentasi
└── PRD-auth-ecommerce.md       # 📄 Product Requirements Document
```

---

## 📚 Dokumentasi Tambahan

| Dokumen | Isi |
|---|---|
| 📊 [`FLOW_DATABASE.md`](FLOW_DATABASE.md) | Diagram ERD (Mermaid), relasi, alur checkout, chatbot, ulasan |
| 📽️ [`PRESENTASI_PROYEK.md`](PRESENTASI_PROYEK.md) | Ringkasan proyek siap dijadikan slide presentasi PPT |
| 📄 [`PRD-auth-ecommerce.md`](PRD-auth-ecommerce.md) | Product Requirements Document lengkap |

---

## 📝 Catatan

- **Data contoh** (`php artisan migrate --seed`) berisi akun admin, pelanggan, kategori, produk, ulasan, dan voucher.
- **Foto produk** tersimpan di `public/images/products`.
- Jangan commit file `.env`, password database, atau log. Laravel sudah menanganinya via `.gitignore`.

---

<div align="center">

**VinzyPlay** · Gaming, Diecast & Hobi

Dibuat dengan ❤️ untuk Praktik MSI

</div>
