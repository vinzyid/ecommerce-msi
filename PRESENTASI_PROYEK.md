# DOKUMENTASI LENGKAP PROYEK: VINZYPLAY E-COMMERCE MARKETPLACE

Dokumen ini disusun sebagai panduan menyeluruh arsitektur, fitur, teknologi, dan alur bisnis platform **VinzyPlay** untuk dikonversi menjadi slide presentasi PowerPoint (PPT) profesional melalui AI / GPT.

---

## 1. Ringkasan Eksekutif (Executive Summary)

* **Nama Platform**: VinzyPlay
* **Kategori**: Niche E-Commerce Marketplace (Gaming Gears, Diecast Koleksi, Komponen PC & Hobi)
* **Tagline**: *"Lengkapi Setup Gaming & Koleksi Diecast Kamu"*
* **Visi**: Menghadirkan ekosistem belanja daring khusus hobi dan gaming di Indonesia yang aman, interaktif, transparan, dan dilengkapi kecerdasan buatan (AI Customer Assistant 24/7).
* **Target Pengguna**: Gamers, PC builder/enthusiast, kolektor diecast (Hot Wheels, Mini GT, Inno64), dan kolektor anime figure.

---

## 2. Arsitektur & Tumpukan Teknologi (Technology Stack)

* **Backend Framework**: Laravel 12.x (PHP 8.4)
  * Arsitektur MVC modular, Eloquent ORM, Database Transactions, dan Service Pattern.
* **Frontend**: Blade Templating + Tailwind CSS v4 + Vanilla JS (ringan, tanpa framework besar).
* **Build Tooling**: Vite 7.x + Laravel Vite Plugin.
* **Database**: PostgreSQL 16 pada Neon Serverless Cloud.
* **AI & LLM Integration**: REST API kompatibel OpenAI (GripHub Router) dengan konteks katalog dinamis.

---

## 3. Fitur Utama & Keunggulan Produk (Core Features)

### A. Storefront (Gaya Tokopedia & Shopee)
1. **Top Utility Bar**: unduh aplikasi, mitra seller, gratis ongkir nasional, CS 24/7.
2. **Trending Search Bar**: pencarian case-insensitive (`ILIKE`), multi-kata, lintas kategori & SKU, plus tag pencarian populer.
3. **Hero 3-Slot Banner**: slider carousel promosi + 2 kartu promo samping.
4. **Klaim Kupon Toko (Voucher Rail)**: salin kode kupon diskon dengan feedback visual.
5. **Flash Sale & Kejar Diskon**: countdown timer real-time + progress bar penjualan.
6. **Official Brands Pavilion**: brand terkemuka (PlayStation, ASUS ROG, Logitech G, HyperX, Secretlab, Mini GT, MSI, Hot Wheels).
7. **Product Card Modern**: badge Official Store, Bebas Ongkir, lokasi, rating, dan jumlah terjual.

### B. Detail Produk & Validasi Pembeli
1. **Galeri Produk Terkurasi**: foto jernih, status diskon, jaminan 100% Original.
2. **Store Card**: profil VinzyPlay Official Store + tombol Chat Penjual.
3. **Estimasi Pengiriman & Opsi Kurir**: JNE, SiCepat, J&T, GoSend + estimasi tiba.
4. **Simulasi Cicilan & Metode Pembayaran**: QRIS, GoPay, BCA, COD.
5. **Sticky Mobile Buy Bar**: tombol beli melayang di layar ponsel.
6. **Ulasan Pembeli Terverifikasi**: hanya pembeli yang menyelesaikan pesanan yang bisa mengulas, dengan badge "Pembeli Terverifikasi".

### C. Keranjang Belanja Pintar
1. **Seleksi Barang Fleksibel**: centang barang yang ingin dibeli / Pilih Semua.
2. **Kalkulasi Real-time**: subtotal, total, dan jumlah barang dihitung instan.
3. **Progress Bar Bebas Ongkir**: sisa belanjaan menuju batas Rp300.000.
4. **Stepper Kuantitas**: tombol minus/plus interaktif.
5. **Beli Sekarang**: langsung ke checkout untuk produk tersebut.

### D. Checkout & Keamanan Transaksi
1. **Form 3-Tahap**: Alamat, Pengiriman, Pembayaran.
2. **Opsi Pengiriman**: Reguler (bebas ongkir min. Rp300rb) / Kilat (Rp25.000).
3. **Opsi Pembayaran**: Transfer Bank Virtual Account (BCA/Mandiri/BRI/BNI) & COD.
4. **Buku Alamat Otomatis**: alamat tersimpan, checkout berikutnya terisi otomatis.
5. **Integritas Data**: hanya barang yang dipilih yang terhapus dari keranjang.

### E. Pelacakan Pengiriman & Siklus Pesanan
1. **Stepper Status**: Menunggu Bayar -> Diproses -> Dikirim -> Selesai.
2. **Instruksi Virtual Account**: nomor VA + tombol salin.
3. **Simulasi Pembayaran Sandbox**: tandai lunas tanpa uang asli.
4. **Penerbitan Resi Otomatis**: kurir + nomor resi.
5. **Timeline Pelacakan Kurir**: log vertikal tahapan pengiriman.
6. **Penyelesaian Pesanan**: konfirmasi terima yang membuka akses ulasan.

### F. Chatbot Asisten AI Toko
1. **Integrasi LLM**: memahami katalog, harga, stok, diskon, spesifikasi.
2. **Guardrail Anti-Offtopic**: hanya menjawab seputar belanja toko.
3. **Memori Percakapan**: mengirim riwayat 8 pesan terakhir agar tidak lupa konteks.
4. **Format Ramah Ponsel**: tanpa tabel markdown rusak; perbandingan dengan poin terstruktur.
5. **Green Computing**: cache pertanyaan mirip menghemat token LLM, listrik (kWh), dan emisi karbon (CO2e).

### G. Kelola Akun Pengguna
1. **Dashboard Profil**: total pesanan, pesanan aktif, total belanja, wishlist.
2. **Tab Interaktif**: Profil, Alamat Saya, Keamanan, Riwayat Pesanan.
3. **Buku Alamat**: tambah/edit/hapus, jadikan alamat utama.
4. **Ganti Password**: terenkripsi bcrypt + tombol lihat password (ikon mata).

---

## 4. Struktur Basis Data (Database Schema Highlights)

1. **`users`**: id, username, email, password, is_admin.
2. **`user_addresses`**: label, recipient_name, phone, address, city, is_default.
3. **`categories`**: name, slug, is_active.
4. **`products`**: category_id, name, slug, sku, price, compare_at_price, badge, stock, is_active, is_featured.
5. **`cart_items`**: user_id, product_id, quantity (unique per user+product).
6. **`orders`**: order_number, customer_name, address, courier, tracking_number, subtotal, discount, total, status.
7. **`order_items`**: product_name, sku, price, quantity, subtotal (snapshot historis).
8. **`vouchers`**: code, type, value, min_spend, max_discount, is_active.
9. **`reviews`**: product_id, user_id, rating, comment (1 per user+product).
10. **`wishlists`**: user_id, product_id.
11. **`chatbot_messages`**: question_hash, canonical_key, answer, hit_count, expires_at.

---

## 5. Alur Transaksi End-to-End

```text
[ Katalog / Pencarian ]
        |
        v
[ Pilih Produk ] --(Beli Sekarang)--
        |                            |
        v                            |
[ Keranjang Belanja ]                |
        |                            |
        v                            |
[ Centang Item Tertentu ]            |
        |                            |
        v                            |
[ Checkout ] <-----------------------
        |
        |-- Isi Alamat & Pilih Kurir
        |-- Pilih Pembayaran (VA / COD)
        v
[ Buat Pesanan ] --> Status: Menunggu Bayar
        |
        |-- (Simulasi Bayar / Transfer VA)
        v
[ Status: Diproses Penjual ]
        |
        |-- (Simulasi Kirim & Terbit Resi)
        v
[ Status: Sedang Dikirim ]
        |
        |-- Cek Timeline Pelacakan
        |-- Pembeli Terima Paket
        v
[ Status: Selesai ] --> Tulis Ulasan Produk
```

---

## 6. Template Prompt Rekomendasi untuk GPT ke PowerPoint

> Salin teks di bawah ini ke ChatGPT untuk membuat presentasi slide:

```text
Bertindaklah sebagai Senior Product Manager dan Slide Presentation Designer.
Gunakan konten dokumen "VINZYPLAY E-COMMERCE MARKETPLACE" di atas untuk membuat outline slide presentasi PowerPoint (10-12 slide) dengan format:

1. Judul Slide
2. Sub-judul / Key Message
3. Poin-poin Utama (Bullet points ringkas, to-the-point)
4. Visual Suggestion (Ide grafik, icon, diagram, atau screenshot yang cocok)

Slide yang diinginkan:
- Slide 1: Cover & Pengenalan Brand VinzyPlay
- Slide 2: Latar Belakang Masalah & Peluang Pasar (Gaming & Diecast Niche)
- Slide 3: Solusi & Nilai Utama VinzyPlay
- Slide 4: Tumpukan Teknologi (Laravel 12, Tailwind v4, PostgreSQL Neon, Vite)
- Slide 5: Storefront Modern & Pengalaman Belanja (Tokopedia/Shopee Look)
- Slide 6: Smart Cart & Fitur Pemilihan Barang (Partial Checkout)
- Slide 7: Checkout & Manajemen Pengiriman (Kurir, Resi & Tracking)
- Slide 8: Integrasi AI Chatbot Asisten Toko & Keunggulannya
- Slide 9: Sistem Keamanan & Ulasan Terverifikasi (Trust & Anti-Fraud)
- Slide 10: Arsitektur Database & Alur Transaksi End-to-End
- Slide 11: Green Computing & Efisiensi Energi AI
- Slide 12: Kesimpulan, Nilai Tambah & Rencana Pengembangan Mendatang
```

---
*Dokumen ini disesuaikan dengan implementasi kode sumber aktual VinzyPlay E-Commerce.*
