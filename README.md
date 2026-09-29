# Praktik MSI: Redesign Website E-Commerce

Redesign antarmuka dan penambahan fitur pada website e-commerce berbasis Laravel untuk tugas Praktik MSI. Aplikasi mencakup katalog produk, autentikasi pelanggan, wishlist, cart, checkout, ulasan, riwayat pesanan, dan panel admin.

## Nama Toko

Nama toko tidak ditulis di dalam kode. Nama diambil dari `APP_NAME` pada `.env` dan dipakai otomatis pada judul halaman, logo, serta footer.

```dotenv
APP_NAME="Nama Toko Anda"
```

Untuk mengganti nama toko, cukup ubah `APP_NAME`. Tidak perlu menyentuh kode atau tampilan.

## Developer

**Rafi Pandya P**

## Fitur

### Pengunjung

- Katalog produk dengan foto
- Pencarian dari header atau katalog berdasarkan nama, SKU, dan deskripsi
- Filter dan halaman kategori
- Halaman promo berisi produk diskon dan kode voucher
- Halaman tentang
- Membaca ulasan pembeli

### Pelanggan

- Registrasi dan login dengan email atau username
- Wishlist dengan ikon hati pada tiap produk
- Cart tersimpan per akun, dengan opsi menghapus seluruh isi
- Kode promo dengan validasi minimum belanja dan batas potongan
- Checkout bertahap: alamat, pengiriman, pembayaran, konfirmasi
- Ongkir reguler Rp15.000 gratis mulai Rp300.000, atau pengiriman kilat Rp25.000
- Pembayaran COD atau transfer bank
- Menulis dan mengubah ulasan produk
- Riwayat serta detail pesanan

### Admin

- Ringkasan angka dan tiga grafik: produk per kategori, pendapatan bulanan, status pesanan
- Pengelolaan kategori
- Pengelolaan produk, harga, harga promo, foto, stok, dan atribut produk
- Pengaturan produk aktif, rekomendasi, serta label produk
- Pengelolaan pengguna dan peran akun
- Pengelolaan voucher
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

## Data Contoh

`php artisan migrate --seed` membuat:

- 1 akun admin dan 3 akun pelanggan
- 4 kategori
- 16 produk dengan variasi harga, harga promo, label, dan atribut
- ulasan contoh untuk setiap produk
- 3 kode voucher: `HEMAT10`, `GRATIS15`, dan `DISKON25`

Password akun pelanggan development: `Password123!`

## Struktur Modul

```text
app/
├── Http/Controllers/
│   ├── Admin/
│   └── Auth/
├── Http/Middleware/
├── Models/
└── Support/

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
├── errors/
├── layouts/
├── orders/
├── pages/
├── partials/
└── wishlist/

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
