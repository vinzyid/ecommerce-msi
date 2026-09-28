# PRD Website E-Commerce NADI Market

## 1. Tujuan

NADI Market merupakan aplikasi e-commerce untuk praktikum Aplikasi Web. Pengunjung dapat melihat katalog, kategori, dan promo. Pelanggan dapat membuat akun, mengelola wishlist, cart, checkout, menulis ulasan, dan memeriksa pesanan. Admin mengelola kategori, produk, stok, voucher, pengguna, serta status pesanan.

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

- Melihat katalog, kategori, halaman promo, dan detail produk
- Mencari produk dari header maupun katalog
- Memfilter produk berdasarkan kategori
- Membaca ulasan pembeli
- Membuka halaman login dan registrasi
- Membaca halaman tentang

### Pelanggan

Pelanggan memiliki seluruh akses pengunjung, ditambah:

- Mengelola wishlist
- Menambah dan mengubah isi cart, termasuk menghapus seluruh isi cart
- Memakai kode promo pada cart
- Checkout dengan alamat dan metode pengiriman lengkap
- Menulis serta mengubah ulasan produk
- Melihat daftar serta detail pesanannya
- Melihat profil, statistik belanja, dan riwayat pesanan di halaman akun
- Keluar dari akun

### Admin

Admin memiliki seluruh akses pelanggan, ditambah:

- Melihat ringkasan toko beserta grafik
- Membuat dan mengubah kategori
- Membuat, mengubah, dan menonaktifkan produk, termasuk harga promo dan atribut produk
- Mengubah stok produk
- Melihat dan mengelola pengguna serta perannya
- Melihat semua pesanan
- Mengubah status pesanan

Kolom `users.is_admin` menentukan akses admin. Middleware `admin` melindungi seluruh route `/admin`.

Profil pengguna memakai avatar inisial yang dihitung dari username. Aplikasi tidak menyimpan atau mengunggah foto.

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
| compare_at_price | decimal(12,2) | nullable, harga sebelum diskon |
| badge | varchar(30) | nullable, label seperti Best Seller |
| weight_grams | unsigned integer | nullable |
| material | varchar(100) | nullable |
| color | varchar(60) | nullable |
| dimensions | varchar(80) | nullable |
| stock | unsigned integer | minimal 0 |
| image_url | varchar(500) | nullable |
| is_active | boolean | default true |
| is_featured | boolean | default false |
| timestamps | timestamp | bawaan Laravel |

Produk dianggap sedang diskon bila `compare_at_price` terisi dan lebih besar dari `price`.

### reviews

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| product_id | bigint | foreign key products, cascade delete |
| user_id | bigint | foreign key users, cascade delete |
| rating | tinyint | 1 sampai 5 |
| comment | varchar(500) | nullable |
| is_approved | boolean | default true |
| timestamps | timestamp | bawaan Laravel |

Pasangan `product_id` dan `user_id` unik sehingga satu pelanggan hanya punya satu ulasan per produk.

### wishlists

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| user_id | bigint | foreign key users, cascade delete |
| product_id | bigint | foreign key products, cascade delete |
| timestamps | timestamp | bawaan Laravel |

Pasangan `user_id` dan `product_id` unik.

### vouchers

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id | bigint | primary key |
| code | varchar(30) | unik |
| description | varchar(150) | nullable |
| type | varchar(20) | `percent` atau `fixed` |
| value | unsigned integer | persen atau rupiah |
| min_spend | unsigned integer | default 0 |
| max_discount | unsigned integer | nullable, batas potongan |
| usage_limit | unsigned integer | nullable |
| used_count | unsigned integer | default 0 |
| is_active | boolean | default true |
| expires_at | timestamp | nullable |
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
| province | varchar(80) | nullable |
| city | varchar(80) | nullable |
| district | varchar(80) | nullable |
| postal_code | varchar(10) | nullable |
| shipping_method | varchar(20) | `regular` atau `express` |
| notes | varchar(500) | nullable |
| payment_method | varchar(20) | `cod` atau `bank_transfer` |
| voucher_id | bigint | nullable, foreign key vouchers |
| voucher_code | varchar(30) | nullable, snapshot kode |
| subtotal | decimal(12,2) | snapshot nilai cart |
| discount | unsigned integer | default 0 |
| shipping_cost | decimal(12,2) | Rp15.000 reguler, Rp25.000 kilat, gratis mulai Rp300.000 |
| total | decimal(12,2) | subtotal dikurangi diskon ditambah ongkir |
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
GET /kategori                 daftar kategori
GET /promo                    produk diskon dan kode promo
GET /tentang                  informasi toko
GET /products/{product:slug} detail produk
```

Katalog hanya menampilkan kategori dan produk aktif. Query `q` mencari nama, SKU, dan deskripsi. Query `category` memfilter slug kategori. Setiap halaman memuat paling banyak 12 produk.

Header menampilkan kolom pencarian yang mengirim ke katalog. Kartu produk menampilkan label diskon, label produk, rata-rata rating, jumlah ulasan, harga, harga sebelum diskon bila ada, dan sisa stok.

Detail produk menampilkan galeri gambar, harga dan harga sebelum diskon, ringkasan rating, atribut produk (berat, material, warna, dimensi), tombol tambah ke cart, tombol beli sekarang, tombol wishlist, dan daftar ulasan. Produk dengan stok nol tidak dapat masuk ke cart.

## 7. Cart

Route cart memakai middleware `auth`:

```text
GET    /cart
POST   /cart
DELETE /cart                 menghapus seluruh isi cart
PATCH  /cart/{cartItem}
DELETE /cart/{cartItem}
POST   /voucher              memasang kode promo
DELETE /voucher              menghapus kode promo
```

- Cart tersimpan di database per pelanggan.
- Menambahkan produk yang sama menaikkan quantity item lama.
- Quantity tidak boleh melebihi stok.
- Pelanggan hanya dapat mengubah item miliknya.
- Total cart dihitung ulang dari harga produk. Aplikasi tidak mempercayai harga dari request.
- Kode promo divalidasi keaktifan, masa berlaku, batas pemakaian, dan minimum belanja. Kode disimpan di session, bukan di form.
- Ringkasan menampilkan subtotal, diskon, ongkos kirim, dan total.

## 7.1 Wishlist

```text
GET    /wishlist
POST   /wishlist/{product:slug}
DELETE /wishlist/{product:slug}
DELETE /wishlist             mengosongkan wishlist
```

## 7.2 Ulasan

```text
POST   /products/{product:slug}/reviews
DELETE /reviews/{review}
```

Satu pelanggan hanya punya satu ulasan per produk. Mengirim ulasan baru memperbarui ulasan lama. Pelanggan hanya dapat menghapus ulasannya sendiri, admin dapat menghapus semua.

## 8. Checkout

Route checkout memakai middleware `auth`:

```text
GET  /checkout
POST /checkout
```

Input: nama penerima, nomor telepon, alamat, provinsi, kota, kecamatan, kode pos, metode pengiriman, catatan opsional, dan metode pembayaran.

Checkout berjalan dalam transaksi database:

1. Sistem mengunci baris produk dengan `lockForUpdate()`.
2. Sistem memeriksa ulang status produk dan stok.
3. Sistem memvalidasi kode promo dan menghitung diskon.
4. Sistem membuat order serta snapshot order item.
5. Sistem mengurangi stok.
6. Sistem menaikkan penghitung pemakaian voucher.
7. Sistem menghapus cart.

Jika stok berubah, transaksi dibatalkan dan pelanggan kembali ke cart dengan pesan kesalahan.

Ongkos kirim: reguler Rp15.000 gratis mulai Rp300.000 setelah diskon, kilat Rp25.000.

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
GET      /admin/users
GET      /admin/users/{user}
PATCH    /admin/users/{user}/role
```

Dashboard menampilkan ringkasan angka dan tiga grafik: produk per kategori, pendapatan enam bulan terakhir, serta komposisi status pesanan. Grafik dirender dengan CSS murni, tanpa pustaka chart.

Admin dapat mencari pengguna, membuka detail beserta riwayat pesanannya, dan mengubah peran akun. Admin tidak dapat menurunkan peran akunnya sendiri.

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
- ikon garis SVG untuk navigasi, dirender tanpa pustaka ikon
- grafik dashboard dirender dengan CSS murni
- copy literal yang menjelaskan tindakan

Hindari headline puitis, glassmorphism, blob, serif miring, teks raksasa, ikon dekoratif, dan slogan abstrak. Gradient dipakai terbatas hanya untuk latar panel gelap dan pengisian grafik.

## 13. Seed Data

Seeder membuat:

- satu akun admin dan tiga akun pelanggan
- empat kategori
- belasan produk dengan variasi stok, harga, harga promo, label, dan atribut
- ulasan contoh untuk setiap produk
- tiga kode promo: `HEMAT10`, `GRATIS15`, dan `NADI25`

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
- Kode promo yang tidak valid atau kedaluwarsa ditolak.
- Checkout membuat snapshot item, mengurangi stok, dan membersihkan cart dalam satu transaksi.
- Pelanggan tidak dapat membaca cart atau pesanan milik akun lain.
- Pelanggan dapat mengelola wishlist dan menulis ulasan.
- User biasa menerima HTTP 403 saat membuka `/admin`.
- Admin dapat mengelola kategori, produk, stok, dan status pesanan.
- Admin dapat mencari pengguna serta mengubah peran akun.
- Dashboard admin menampilkan ringkasan angka dan tiga grafik.
- Seluruh feature test utama lulus.

## 15. Batasan

Versi ini belum mencakup payment gateway, upload file, voucher, ulasan, wishlist, kurir eksternal, pelacakan resi, reset password, dan verifikasi email.
