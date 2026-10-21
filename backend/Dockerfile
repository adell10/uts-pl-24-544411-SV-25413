# Laravel + FrankenPHP (pengganti php artisan serve / PHP-FPM + Nginx)
# Versi image ditulis jelas, bukan "latest".
FROM dunglas/frankenphp:1.13.1-php8.4

# Ekstensi PHP yang dibutuhkan Laravel + MySQL
RUN install-php-extensions pdo_mysql zip intl opcache pcntl

# Composer diambil dari image resmi (versi jelas)
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1) Salin composer.json + composer.lock DULU, lalu install dependency.
#    Lapisan ini di-cache selama dua file ini tidak berubah.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

# 2) Baru salin kode aplikasi
COPY . .

# 3) Selesaikan autoload + siapkan folder yang perlu ditulis
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

# Tidak ada nilai rahasia di sini: APP_KEY, DB_PASSWORD, dll. dikirim lewat environment saat container jalan.
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

EXPOSE 8000

ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/app/docker/Caddyfile"]