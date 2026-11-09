# Deploy VinzyPlay ke VPS Tencent

VinzyPlay dipasang memakai Docker + Caddy, dan bisa berbagi VPS dengan aplikasi lain:

- container web hanya mendengarkan `127.0.0.1:8081` (8080 dibiarkan untuk aplikasi lain);
- konfigurasi Caddy-nya terpisah di `/etc/caddy/sites/vinzyplay.caddy`.

Database tetap di **Neon** (cloud). Di VPS hanya ada container dan volume foto unggahan.

---

## 1. DNS (di panel domain vinzyplay.my.id)

Cukup **satu domain**. Admin tetap di `https://vinzyplay.my.id/admin`, jadi tidak perlu subdomain admin.

| Type | Name  | Value              |
|------|-------|--------------------|
| A    | `@`   | `<IP_PUBLIK_VPS>`  |
| A    | `www` | `<IP_PUBLIK_VPS>`  |

`www` otomatis dialihkan ke `vinzyplay.my.id`. Cek dari VPS: `dig +short vinzyplay.my.id` harus membalas IP VPS.

Port 80 & 443 harus terbuka di Security Group (kalau VPS sudah dipakai situs HTTPS lain, biasanya sudah).

## 2. Google Cloud Console (login Google)

Di OAuth Client yang dipakai VinzyPlay, tambahkan:

- **Authorized JavaScript origins:** `https://vinzyplay.my.id`
- **Authorized redirect URIs:** `https://vinzyplay.my.id/auth/google/callback`

URI `localhost` boleh tetap ada supaya development di laptop masih jalan.

## 3. Pemasangan pertama (sekali saja)

```bash
ssh ubuntu@<IP_PUBLIK_VPS>

git clone https://github.com/vinzyid/ecommerce-msi.git ~/vinzyplay
cd ~/vinzyplay
bash deploy/setup-vps.sh          # tekan Enter untuk vinzyplay.my.id
nano .env.production              # isi DB_*, MAIL_*, GOOGLE_*, CHATBOT_* dari .env laptop
./deploy.sh
```

> Repo privat? Saat `git clone` diminta password, masukkan GitHub Personal Access Token.

`setup-vps.sh` tidak menyentuh situs lain: ia hanya menambahkan satu baris
`import /etc/caddy/sites/*.caddy` ke Caddyfile utama (cadangan `.bak-*` dibuat
dulu) dan memvalidasi konfigurasi sebelum Caddy dimuat ulang.

## 4. Update berikutnya

Di laptop: commit & push ke `main`. Lalu di VPS:

```bash
cd ~/vinzyplay && ./deploy.sh
```

Skrip ini menarik kode, build image, menjalankan migrasi, mengganti container,
dan menunggu sampai situs merespons.

---

## Perintah harian (dari `~/vinzyplay`)

| Tujuan            | Perintah                                         |
|-------------------|--------------------------------------------------|
| Log situs         | `docker compose logs -f web`                     |
| Log antrean email | `docker compose logs -f queue`                   |
| Status            | `docker compose ps`                              |
| Artisan           | `docker compose exec web php artisan <perintah>` |
| Restart           | `docker compose restart`                         |
| Seed data awal    | `docker compose exec web php artisan db:seed`    |

## Pemecahan masalah

**HTTPS gagal / sertifikat tidak terbit** — pastikan A record sudah benar (`dig +short vinzyplay.my.id`), lalu lihat `sudo journalctl -u caddy -n 50 --no-pager`.

**502 Bad Gateway** — container web belum siap atau mati: `docker compose logs --tail=100 web`.

**Email tidak terkirim** — `MAIL_PASSWORD` harus App Password 16 huruf **tanpa spasi**. Cek `docker compose logs queue` dan `docker compose exec web tail -50 storage/logs/laravel.log`.

**Login Google "redirect_uri_mismatch"** — redirect URI di Google Cloud Console belum sama persis dengan `https://vinzyplay.my.id/auth/google/callback`.

**Build kehabisan memori (`Killed`)** — tambahkan swap 2 GB:
```bash
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

## Keamanan

- `.env.production` tidak pernah di-commit (sudah di `.gitignore`) dan diset `chmod 600`.
- `APP_DEBUG=false` di produksi.
- Port `8081` hanya terbuka di localhost; jangan dibuka di Security Group.
- Pastikan akun admin produksi memakai password kuat, bukan password dari seeder.
