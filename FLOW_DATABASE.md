# FLOW & DIAGRAM DATABASE — VINZYPLAY E-COMMERCE

> Dokumen ini menjelaskan **seluruh struktur database**, **relasi antar tabel**, **alur data transaksi**, serta **diagram visual (Mermaid)** yang bisa langsung dirender di GitHub, VS Code, Notion, Obsidian, atau Mermaid Live Editor.

---

## 1. Ringkasan Database

* **DBMS**: PostgreSQL 16 (Neon Serverless Cloud)
* **Tabel inti aplikasi**: 13 tabel
* **Tabel bawaan Laravel**: `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`
* **Karakteristik**: transaksi ACID, foreign key dengan kebijakan cascade/restrict/set-null, unique composite anti-duplikasi, dan index untuk performa.

---

## 2. Daftar Tabel & Fungsinya

| # | Tabel | Fungsi |
|---|---|---|
| 1 | `users` | Akun pengguna (pelanggan & admin) |
| 2 | `user_addresses` | Buku alamat pengiriman tersimpan (auto-isi checkout) |
| 3 | `categories` | Kategori produk (Keyboard, Audio, Diecast, dll) |
| 4 | `products` | Katalog produk |
| 5 | `cart_items` | Isi keranjang belanja per pengguna |
| 6 | `orders` | Header pesanan (alamat, kurir, resi, status, total) |
| 7 | `order_items` | Rincian barang per pesanan (snapshot harga) |
| 8 | `vouchers` | Kode promo & diskon |
| 9 | `reviews` | Ulasan & rating (khusus pembeli terverifikasi) |
| 10 | `wishlists` | Produk favorit pengguna |
| 11 | `chatbot_messages` | Cache jawaban chatbot AI (Green Computing) |
| 12 | `sessions` | Sesi login (bawaan Laravel) |
| 13 | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens` | Infrastruktur framework |

---

## 3. Diagram ERD Lengkap (Mermaid)

> Render di https://mermaid.live atau GitHub Markdown.

```mermaid
erDiagram
    USERS ||--o{ USER_ADDRESSES : "punya banyak alamat"
    USERS ||--o{ CART_ITEMS : "punya keranjang"
    USERS ||--o{ ORDERS : "membuat pesanan"
    USERS ||--o{ REVIEWS : "menulis ulasan"
    USERS ||--o{ WISHLISTS : "menyimpan wishlist"
    USERS ||--o{ CHATBOT_MESSAGES : "bertanya ke chatbot"

    CATEGORIES ||--o{ PRODUCTS : "menaungi"

    PRODUCTS ||--o{ CART_ITEMS : "dimasukkan keranjang"
    PRODUCTS ||--o{ ORDER_ITEMS : "dibeli"
    PRODUCTS ||--o{ REVIEWS : "diulas"
    PRODUCTS ||--o{ WISHLISTS : "difavoritkan"

    ORDERS ||--|{ ORDER_ITEMS : "berisi"
    VOUCHERS ||--o{ ORDERS : "dipakai di"

    USERS {
        bigint id PK
        varchar username UK
        varchar email UK
        varchar password
        boolean is_admin
        timestamp created_at
    }

    USER_ADDRESSES {
        bigint id PK
        bigint user_id FK
        varchar label
        varchar recipient_name
        varchar phone
        text address
        varchar province
        varchar city
        varchar district
        varchar postal_code
        boolean is_default
    }

    CATEGORIES {
        bigint id PK
        varchar name UK
        varchar slug UK
        varchar description
        boolean is_active
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        varchar name
        varchar slug UK
        varchar sku UK
        text description
        decimal price
        decimal compare_at_price
        varchar badge
        int stock
        int weight_grams
        varchar material
        varchar color
        varchar dimensions
        varchar image_url
        boolean is_active
        boolean is_featured
    }

    CART_ITEMS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        int quantity
    }

    ORDERS {
        bigint id PK
        varchar order_number UK
        bigint user_id FK
        varchar customer_name
        varchar phone
        text address
        varchar province
        varchar city
        varchar district
        varchar postal_code
        varchar shipping_method
        varchar courier
        varchar tracking_number
        varchar payment_method
        bigint voucher_id FK
        varchar voucher_code
        decimal subtotal
        int discount
        decimal shipping_cost
        decimal total
        varchar status
        timestamp ordered_at
        timestamp shipped_at
        timestamp completed_at
        varchar notes
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        varchar product_name
        varchar sku
        decimal price
        int quantity
        decimal subtotal
    }

    VOUCHERS {
        bigint id PK
        varchar code UK
        varchar description
        varchar type
        int value
        int min_spend
        int max_discount
        int usage_limit
        int used_count
        boolean is_active
        timestamp expires_at
    }

    REVIEWS {
        bigint id PK
        bigint product_id FK
        bigint user_id FK
        tinyint rating
        varchar comment
        boolean is_approved
    }

    WISHLISTS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
    }

    CHATBOT_MESSAGES {
        bigint id PK
        bigint user_id FK
        varchar question
        varchar question_hash UK
        varchar canonical_key
        text answer
        boolean from_ai
        int hit_count
        timestamp expires_at
    }
```

---

## 4. Relasi & Kebijakan Foreign Key (ON DELETE)

| Tabel. Kolom | Mengarah ke | ON DELETE | Makna Bisnis |
|---|---|---|---|
| `products.category_id` | `categories.id` | **RESTRICT** | Kategori tidak bisa dihapus jika masih ada produk |
| `cart_items.user_id` | `users.id` | **CASCADE** | Hapus user → keranjang terhapus |
| `cart_items.product_id` | `products.id` | **CASCADE** | Hapus produk → hilang dari keranjang |
| `orders.user_id` | `users.id` | **RESTRICT** | Riwayat pesanan dilindungi (user tak bisa dihapus) |
| `order_items.order_id` | `orders.id` | **CASCADE** | Hapus order → rincian ikut terhapus |
| `order_items.product_id` | `products.id` | **SET NULL** | Hapus produk → riwayat pesanan tetap tersimpan |
| `orders.voucher_id` | `vouchers.id` | **SET NULL** | Hapus voucher → order tetap valid |
| `reviews.product_id` | `products.id` | **CASCADE** | Hapus produk → ulasannya ikut terhapus |
| `reviews.user_id` | `users.id` | **CASCADE** | Hapus user → ulasannya ikut terhapus |
| `wishlists.user_id` | `users.id` | **CASCADE** | Hapus user → wishlist terhapus |
| `wishlists.product_id` | `products.id` | **CASCADE** | Hapus produk → hilang dari wishlist |
| `chatbot_messages.user_id` | `users.id` | **SET NULL** | Cache chatbot tetap ada (anonymized) |
| `user_addresses.user_id` | `users.id` | **CASCADE** | Hapus user → buku alamat terhapus |

**Catatan:** `sessions.user_id` hanya punya INDEX tanpa FK constraint.

---

## 5. Unique Constraints (Anti Duplikasi)

| Tabel | Kolom Unik | Tujuan |
|---|---|---|
| `users` | `username` | Username tidak boleh sama |
| `users` | `email` | Email tidak boleh sama |
| `categories` | `name`, `slug` | Kategori unik |
| `products` | `sku`, `slug` | Kode produk & URL unik |
| `cart_items` | `(user_id, product_id)` | 1 produk hanya 1 baris di keranjang |
| `orders` | `order_number` | Nomor pesanan unik |
| `reviews` | `(product_id, user_id)` | **1 ulasan per user per produk** |
| `wishlists` | `(user_id, product_id)` | Produk tidak dobel di wishlist |
| `vouchers` | `code` | Kode voucher unik |
| `chatbot_messages` | `question_hash` | 1 cache per hash pertanyaan |

---

## 6. Index untuk Optimasi Performa

| Tabel | Index | Fungsi |
|---|---|---|
| `products` | `(is_active, category_id)` | Filter produk aktif per kategori |
| `orders` | `(user_id, ordered_at)` | Daftar pesanan user terurut |
| `orders` | `(status, ordered_at)` | Filter pesanan per status |
| `reviews` | `(product_id, is_approved)` | Rating rata-rata produk |
| `chatbot_messages` | `expires_at` | Lookup cache yang masih fresh |
| `chatbot_messages` | `canonical_key` | Pencocokan pertanyaan mirip (Green Computing) |
| `user_addresses` | `(user_id, is_default)` | Ambil alamat utama user dengan cepat |

---

## 7. ALUR DATA: CHECKOUT & PESANAN

```mermaid
flowchart TD
    A["User pilih produk"] --> B["POST /cart (CartController@store)"]
    B --> C["Insert/update cart_items<br/>(unique user_id + product_id)"]
    C --> D["User centang barang yang dibeli"]
    D --> E["GET /checkout?items=ID<br/>(CheckoutController@create)"]
    E --> F["Ambil user_addresses<br/>(defaultAddress auto-isi form)"]
    F --> G["User pilih kurir + pembayaran"]
    G --> H["POST /checkout (store)<br/>DB::transaction()"]

    H --> I["1. Ambil products (validasi stok)"]
    I --> J["2. Hitung subtotal, diskon voucher, ongkir"]
    J --> K["3. INSERT orders (status=pending)"]
    K --> L["4. INSERT order_items (snapshot harga)"]
    L --> M["5. UPDATE products.stock (decrement)"]
    M --> N["6. UPDATE vouchers.used_count"]
    N --> O["7. DELETE cart_items yang dibeli saja"]
    O --> P["8. Simpan ke user_addresses (auto)"]
    P --> Q["Redirect ke /orders/{id}"]

    Q --> R{"Status Pesanan"}
    R -->|Simulasi Bayar| S["orders.status = processing"]
    R -->|Penjual Kirim| T["orders.status = shipped<br/>+ courier + tracking_number + shipped_at"]
    R -->|Pembeli Terima| U["orders.status = completed<br/>+ completed_at"]
    U --> V["User buka produk lalu tulis reviews<br/>(hanya jika sudah pernah beli)"]
```

---

## 8. ALUR DATA: CHATBOT AI & GREEN COMPUTING

```mermaid
flowchart TD
    A["User kirim pertanyaan<br/>+ 8 riwayat percakapan terakhir"] --> B{"Guard Anti-Offtopic?"}
    B -->|Ya, di luar toko| C["Tolak sopan<br/>(hemat token)"]
    B -->|Tidak| D["Hitung question_hash (SHA-256)<br/>+ canonical_key"]

    D --> E{"Pertanyaan lanjutan<br/>kontekstual?"}
    E -->|Ya| G["Panggil AI dengan history"]
    E -->|Tidak| F{"Cache cocok?"}

    F -->|Exact hash| H["GREEN COMPUTING HIT"]
    F -->|Exact canonical_key| H
    F -->|Similarity >= 65%| H
    F -->|Tidak ada| G

    H --> I["hit_count++<br/>(hemat ~3.100 token)"]
    I --> J["Return jawaban cache<br/>tanpa panggil AI"]

    G --> K["POST ke LLM API"]
    K --> L["Simpan ke chatbot_messages<br/>(question_hash, canonical_key, expires_at)"]
    L --> M["Return jawaban AI"]
```

---

## 9. ALUR DATA: ULASAN TERVERIFIKASI

```mermaid
flowchart TD
    A["User buka halaman produk"] --> B{"Sudah login?"}
    B -->|Belum| C["Tampilkan: Masuk untuk menulis ulasan"]
    B -->|Sudah| D{"Pernah beli produk ini?"}

    D --> E["Cek: orders.status != cancelled<br/>AND order_items.product_id = produk"]
    E -->|Tidak| F["Tampilkan info:<br/>Hanya pembeli terverifikasi yang bisa mengulas"]
    E -->|Ya| G["Tampilkan form ulasan +<br/>badge Pembeli Terverifikasi"]

    G --> H["POST /products/{id}/reviews"]
    H --> I["Validasi ulang di backend<br/>(ReviewController@store)"]
    I --> J["updateOrCreate reviews<br/>(unique product_id + user_id)"]
```

---

## 10. Kamus Kolom Penting (Status & Enum)

### `orders.status`
| Nilai | Label | Arti |
|---|---|---|
| `pending` | Menunggu | Pesanan dibuat, belum dibayar |
| `processing` | Diproses | Lunas, penjual menyiapkan barang |
| `shipped` | Dikirim | Paket diserahkan kurir + nomor resi terbit |
| `completed` | Selesai | Paket diterima pembeli |
| `cancelled` | Dibatalkan | Pesanan dibatalkan |

### `orders.payment_method`
| Nilai | Arti |
|---|---|
| `bank_transfer` | Transfer Bank / Virtual Account |
| `cod` | Bayar di Tempat |

### `orders.shipping_method`
| Nilai | Arti |
|---|---|
| `regular` | Kurir reguler (2-4 hari, bebas ongkir min. Rp300rb) |
| `express` | Kurir kilat (1 hari, Rp25.000) |

### `vouchers.type`
| Nilai | Arti |
|---|---|
| `percent` | Diskon persentase (mis. 10% -> value = 10) |
| `fixed` | Potongan nominal tetap (mis. Rp15.000 -> value = 15000) |

### `chatbot_messages` (Green Computing)
| Kolom | Arti |
|---|---|
| `question_hash` | SHA-256 dari pertanyaan ternormalisasi (exact match) |
| `canonical_key` | Kata kunci baku terurut (mis. `harga playstation 5`) untuk pertanyaan mirip |
| `hit_count` | Berapa kali jawaban dipakai ulang (basis hemat token & energi) |
| `expires_at` | Batas waktu cache (default 60 menit) |

---

## 11. Diagram Sederhana (Teks ASCII) untuk Presentasi Cepat

```
                        +-------------+
                        |   USERS     |
                        +------+------+
             +-------------+---+----+-----------+------------+
             v             v        v           v            v
     +--------------+ +---------+ +--------+ +--------+ +--------------+
     | USER_ADDRESS | |CART_ITEM| | ORDERS | |REVIEWS | |  WISHLISTS   |
     +--------------+ +----+----+ +---+----+ +---+----+ +------+-------+
                           |          |          |             |
                           v          v          |             |
                     +----------+ +------------+ |             |
                     | PRODUCTS |<|ORDER_ITEMS | +-------------+
                     +----+-----+ +------------+
                          |
                  +-------+--------+
                  v                v
           +------------+   +------------+
           | CATEGORIES |   |  VOUCHERS  |<-- (voucher_id di ORDERS)
           +------------+   +------------+

     +-----------------------+
     |  CHATBOT_MESSAGES     |  <-- Cache jawaban AI (Green Computing)
     |  question_hash (UK)   |
     |  canonical_key (IDX)  |
     |  hit_count            |
     |  expires_at (IDX)     |
     +-----------------------+
```

---

## 12. Kesimpulan Arsitektur

1. **Normalisasi data**: kategori, produk, pesanan, dan item pesanan dipisah mengikuti bentuk 3NF.
2. **Integritas referensi**: foreign key dengan kebijakan tepat (CASCADE untuk data transien, RESTRICT untuk data historis, SET NULL untuk referensi opsional).
3. **Snapshot historis**: `order_items` menyimpan nama, SKU, dan harga produk saat pembelian agar riwayat tidak berubah walau produk diedit/dihapus.
4. **Anti-duplikasi**: unique composite menjamin satu ulasan, satu wishlist, dan satu baris keranjang per produk per user.
5. **Green Computing**: `chatbot_messages` menyimpan `question_hash`, `canonical_key`, dan `hit_count` untuk mengukur penghematan token LLM, energi listrik (kWh), dan emisi karbon (CO2e).
6. **Auto-fill alamat**: `user_addresses` dengan flag `is_default` mempercepat checkout berulang.

---

*Dokumen ini dibuat berdasarkan struktur migrasi aktual aplikasi VinzyPlay E-Commerce.*
