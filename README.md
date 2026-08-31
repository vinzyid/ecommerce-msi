# Praktik MSI: Website E-Commerce Etalase

Website e-commerce berbasis Laravel untuk tugas Praktik MSI. Aplikasi mencakup katalog produk, autentikasi pelanggan, cart, checkout, riwayat pesanan, dan panel admin.

## Developer

**Rafi Pandya P**

## Fitur

### Pelanggan

- Registrasi dan login dengan email atau username
- Katalog produk dengan foto
- Pencarian berdasarkan nama, SKU, dan deskripsi
- Filter kategori
- Informasi harga dan stok
- Cart tersimpan per akun
- Checkout dengan COD atau transfer bank
- Ongkir Rp15.000 dan gratis mulai Rp300.000
- Riwayat serta detail pesanan

### Admin

- Ringkasan produk, pelanggan, stok rendah, dan pesanan
- Pengelolaan kategori
- Pengelolaan produk, harga, foto, dan stok
- Pengaturan produk aktif serta rekomendasi
- Daftar dan detail pesanan pelanggan
- Perubahan status pesanan

## Teknologi

- PHP 8.2
- Laravel 12
- MySQL atau MariaDB
- Eloquent ORM
- Blade
- CSS
- PHPUnit

## Kebutuhan Sistem

- PHP 8.2 atau lebih baru
- Composer 2
- MySQL atau MariaDB
- XAMPP dapat dipakai untuk database lokal

## Instalasi

Clone repository dan masuk ke folder proyek:

```bash
git clone URL_REPOSITORY_ANDA
cd ecommerce
```

Pasang dependency PHP:

```bash
composer install
```

Buat file environment:

```bash
cp .env.example .env
php artisan key:generate
```

Buat database MySQL:

```sql
CREATE DATABASE ecommerce
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Atur koneksi database di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

Pengguna XAMPP di macOS dapat menambahkan socket berikut:

```dotenv
DB_SOCKET=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock
```

Buat tabel dan data contoh:

```bash
php artisan migrate --seed
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Admin Development

```text
Email    : admin@example.com
Password : Admin123!
```

Panel admin tersedia di `http://127.0.0.1:8000/admin` setelah login.

Akun tersebut hanya untuk development. Ganti password sebelum aplikasi dipasang pada server publik.

## Pengujian

Jalankan seluruh test:

```bash
php artisan test
```

Test memakai SQLite in-memory dan tidak mengubah database MySQL development.

## Struktur Modul

```text
app/
├── Http/Controllers/
│   ├── Admin/
│   └── Auth/
├── Http/Middleware/
└── Models/

database/
├── migrations/
└── seeders/

resources/views/
├── admin/
├── auth/
├── cart/
├── catalog/
├── checkout/
├── components/
├── layouts/
└── orders/

public/
├── css/
└── images/products/
```

## Dokumentasi Produk

Spesifikasi fitur, model data, aturan checkout, dan acceptance criteria tersedia di [`PRD-auth-ecommerce.md`](PRD-auth-ecommerce.md).

## Foto Produk

Foto produk berasal dari [Pexels](https://www.pexels.com/) dan disimpan di `public/images/products`. Penggunaan foto mengikuti lisensi Pexels.

## Catatan GitHub

Jangan memasukkan file `.env`, password database, atau file log ke repository. Laravel sudah mencantumkan file tersebut dalam `.gitignore`.

Folder `vendor` tidak perlu diunggah. Pengguna repository memasang dependency dengan `composer install`.
