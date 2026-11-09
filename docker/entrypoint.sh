#!/bin/sh
#
# Entrypoint container VinzyPlay.
#
#   (tanpa argumen)   -> web server: cache, migrasi, lalu FrankenPHP
#   <perintah lain>   -> worker (queue/scheduler): cache konfigurasi lalu jalankan perintah itu
#
# Migrasi & cache view hanya dikerjakan container web. storage/ dipakai bersama
# lewat volume, jadi worker tidak ikut menulis supaya tidak saling balapan.
set -e

# FrankenPHP membaca alamat dari SERVER_NAME; ":8080" = semua alamat, port 8080.
export SERVER_NAME=":${PORT:-8080}"

# bootstrap/cache ada di dalam image (tidak dibagi), jadi aman di tiap container.
php artisan config:cache
php artisan route:cache
php artisan event:cache

if [ "$#" -gt 0 ]; then
  exec "$@"
fi

# View hasil kompilasi deploy sebelumnya dibuang dulu supaya tampilan baru langsung terpakai.
php artisan view:clear >/dev/null
php artisan view:cache

php artisan migrate --force
php artisan migrate --force --database=eis --path=database/migrations/eis || \
  echo "Peringatan: migrasi database EIS gagal; dashboard EIS akan tampil 'tidak tersedia'." >&2

exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile
