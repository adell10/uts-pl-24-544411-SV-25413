#!/bin/sh
set -e

# Konfigurasi dibaca dari environment (diisi docker-compose dari .env)
php artisan config:cache

# Buat/ubah tabel di MySQL
php artisan migrate --force

# Isi akun demo + data contoh (aman dijalankan berulang)
if [ "${SEED_ON_START:-false}" = "true" ]; then
  php artisan db:seed --force
fi

exec "$@"