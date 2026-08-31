# PRD Website E-Commerce Etalase

## 1. Tujuan

Etalase merupakan aplikasi e-commerce untuk praktikum Aplikasi Web. Pengunjung dapat melihat produk. Pelanggan dapat membuat akun, mengelola cart, checkout, dan memeriksa pesanan. Admin mengelola kategori, produk, stok, serta status pesanan.

## 2. Stack

- Laravel 12 dan PHP 8.2
- MySQL atau MariaDB
- Eloquent ORM
- Blade dan CSS tanpa framework UI
- Session authentication dengan guard `web`
- PHPUnit untuk feature test

Aplikasi tidak memakai Breeze, Jetstream, Fortify, SPA framework, atau payment gateway.

## 3. Peran

### Pengunjung

- Melihat katalog dan detail produk
- Mencari produk
- Memfilter produk berdasarkan kategori
- Membuka halaman login dan registrasi

### Pelanggan

Pelanggan memiliki seluruh akses pengunjung, ditambah:

- Menambah dan mengubah isi cart
- Checkout
- Melihat daftar serta detail pesanannya
- Keluar dari akun

### Admin

Admin memiliki seluruh akses pelanggan, ditambah:

- Melihat ringkasan toko
- Membuat dan mengubah kategori
- Membuat, mengubah, dan menonaktifkan produk
- Mengubah stok produk
- Melihat semua pesanan
- Mengubah status pesanan

Kolom `users.is_admin` menentukan akses admin. Middleware `admin` melindungi seluruh route `/admin`.

## 4. Model Data

### users

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| username | varchar(50) | unik |
| email | varchar(100) | unik |
| password | varchar(255) | hash bcrypt |
| is_admin | boolean | default false |
| timestamps | timestamp | bawaan Laravel |

### categories

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| name | varchar(80) | unik |
| slug | varchar(100) | unik |
| description | varchar(255) | nullable |
| is_active | boolean | default true |
| timestamps | timestamp | bawaan Laravel |

### products

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| category_id | bigint | foreign key categories |
| name | varchar(120) | wajib |
| slug | varchar(150) | unik |
| sku | varchar(50) | unik |
| description | text | wajib |
| price | decimal(12,2) | minimal 0 |
| stock | unsigned integer | minimal 0 |
| image_url | varchar(500) | nullable |
| is_active | boolean | default true |
| is_featured | boolean | default false |
| timestamps | timestamp | bawaan Laravel |

### cart_items

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| user_id | bigint | foreign key users, cascade delete |
| product_id | bigint | foreign key products, cascade delete |
| quantity | unsigned integer | minimal 1 |
| timestamps | timestamp | bawaan Laravel |

Pasangan `user_id` dan `product_id` harus unik.

### orders

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| order_number | varchar(30) | unik |
| user_id | bigint | foreign key users |
| customer_name | varchar(100) | wajib |
| phone | varchar(20) | wajib |
| address | text | wajib |
| notes | varchar(500) | nullable |
| payment_method | varchar(20) | `cod` atau `bank_transfer` |
| subtotal | decimal(12,2) | snapshot nilai cart |
| shipping_cost | decimal(12,2) | Rp15.000, gratis mulai Rp300.000 |
| total | decimal(12,2) | subtotal dan ongkir |
| status | varchar(20) | status pesanan |
| ordered_at | timestamp | waktu checkout |
| timestamps | timestamp | bawaan Laravel |

Status pesanan: `pending`, `processing`, `shipped`, `completed`, atau `cancelled`.

### order_items

Tabel ini menyimpan snapshot produk saat checkout. Perubahan nama atau harga produk tidak mengubah pesanan lama.

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| order_id | bigint | foreign key orders, cascade delete |
| product_id | bigint | nullable, set null saat produk dihapus |
| product_name | varchar(120) | snapshot nama |
| sku | varchar(50) | snapshot SKU |
| price | decimal(12,2) | snapshot harga satuan |
| quantity | unsigned integer | minimal 1 |
| subtotal | decimal(12,2) | harga dikali jumlah |
| timestamps | timestamp | bawaan Laravel |

## 5. Autentikasi

### Registrasi

Input: `username`, `email`, `password`, dan `password_confirmation`.

- Username terdiri dari 3 sampai 50 karakter dan memakai rule `alpha_dash`.
- Email harus valid, maksimal 100 karakter, dan unik.
- Password minimal 6 karakter serta harus cocok dengan konfirmasi.
- Sistem meng-hash password dan memasukkan pelanggan setelah registrasi.
- Blade menampilkan pesan validasi bahasa Indonesia di bawah field terkait.

### Login

Pelanggan dapat memakai email atau username. Pesan gagal tidak menyebut field yang salah. Login meregenerasi session ID.

### Logout

Sistem menghapus autentikasi, membatalkan session, dan meregenerasi token CSRF.

## 6. Katalog

Route publik:

```text
GET /                         katalog
GET /products/{product:slug} detail produk
```

Katalog hanya menampilkan kategori dan produk aktif. Query `q` mencari nama, SKU, dan deskripsi. Query `category` memfilter slug kategori. Setiap halaman memuat paling banyak 12 produk.

Detail produk menampilkan nama, kategori, harga, stok, deskripsi, dan tombol tambah ke cart. Produk dengan stok nol tidak dapat masuk ke cart.

## 7. Cart

Route cart memakai middleware `auth`:

```text
GET    /cart
POST   /cart
PATCH  /cart/{cartItem}
DELETE /cart/{cartItem}
```

- Cart tersimpan di database per pelanggan.
- Menambahkan produk yang sama menaikkan quantity item lama.
- Quantity tidak boleh melebihi stok.
- Pelanggan hanya dapat mengubah item miliknya.
- Total cart dihitung ulang dari harga produk. Aplikasi tidak mempercayai harga dari request.

## 8. Checkout

Route checkout memakai middleware `auth`:

```text
GET  /checkout
POST /checkout
```

Input: nama penerima, nomor telepon, alamat, catatan opsional, dan metode pembayaran.

Checkout berjalan dalam transaksi database:

1. Sistem mengunci baris produk dengan `lockForUpdate()`.
2. Sistem memeriksa ulang status produk dan stok.
3. Sistem membuat order serta snapshot order item.
4. Sistem mengurangi stok.
5. Sistem menghapus cart.

Jika stok berubah, transaksi dibatalkan dan pelanggan kembali ke cart dengan pesan kesalahan.

Aplikasi hanya mencatat pilihan transfer bank. Aplikasi belum menghubungi bank atau memverifikasi pembayaran.

## 9. Pesanan Pelanggan

```text
GET /orders
GET /orders/{order}
```

Pelanggan hanya dapat membuka pesanannya sendiri. Halaman detail menampilkan item, alamat, pembayaran, total, status, dan nomor pesanan.

## 10. Panel Admin

Seluruh route memakai middleware `auth` dan `admin`.

```text
GET      /admin
RESOURCE /admin/categories
RESOURCE /admin/products
GET      /admin/orders
GET      /admin/orders/{order}
PATCH    /admin/orders/{order}/status
```

Admin tidak menghapus kategori atau produk dari antarmuka. Admin memakai status aktif untuk menyembunyikan data tanpa merusak relasi pesanan.

Admin hanya dapat memilih status pesanan yang terdaftar. Pesanan `completed` dan `cancelled` tidak dapat kembali ke status lain.

## 11. Keamanan dan Integritas

- Setiap form memakai token CSRF.
- Blade mencetak data dinamis dengan `{{ }}`.
- Controller memvalidasi seluruh input.
- Middleware memeriksa hak akses admin.
- Checkout memakai transaction dan row lock.
- Harga, subtotal, total, role, serta status tidak diambil mentah dari input pelanggan.
- Foreign key menjaga relasi database.
- Password tidak pernah disimpan atau dicatat dalam bentuk teks.

## 12. Desain

Antarmuka memakai gaya retail formal:

- warna navy, putih, abu-abu, dan aksen perunggu
- serif tegak untuk judul dan sans-serif untuk isi
- foto produk lokal dengan rasio yang konsisten
- grid rapi, border tipis, dan bayangan ringan
- copy literal yang menjelaskan tindakan

Hindari headline puitis, gradient, glassmorphism, blob, serif miring, teks raksasa, ikon dekoratif, dan slogan abstrak.

## 13. Seed Data

Seeder membuat:

- satu akun admin
- empat kategori
- minimal delapan produk dengan variasi stok dan harga

Kredensial admin untuk development:

```text
email: admin@example.com
password: Admin123!
```

## 14. Acceptance Criteria

- Migrasi berjalan di MySQL tanpa error.
- Pengunjung dapat mencari dan memfilter produk aktif.
- Pelanggan dapat mendaftar serta login dengan email atau username.
- Cart menolak quantity yang melebihi stok.
- Checkout membuat snapshot item, mengurangi stok, dan membersihkan cart dalam satu transaksi.
- Pelanggan tidak dapat membaca cart atau pesanan milik akun lain.
- User biasa menerima HTTP 403 saat membuka `/admin`.
- Admin dapat mengelola kategori, produk, stok, dan status pesanan.
- Seluruh feature test utama lulus.

## 15. Batasan

Versi ini belum mencakup payment gateway, upload file, voucher, ulasan, wishlist, kurir eksternal, pelacakan resi, reset password, dan verifikasi email.
