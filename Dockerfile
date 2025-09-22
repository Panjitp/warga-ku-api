# Tahap 1: Builder - Menginstal dependensi Composer
FROM composer:2.7 as builder

WORKDIR /app

# Instal dependensi sistem yang dibutuhkan oleh ekstensi gd, lalu instal ekstensinya
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-install gd

# Salin file dependensi
COPY composer.json composer.lock ./

# Instal dependensi Composer untuk production
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Salin sisa kode aplikasi
COPY . .

# ---------------------------------------------------------------------

# Tahap 2: Final - Image production yang akan dijalankan
FROM php:8.3-fpm-alpine as final

WORKDIR /app

# Instal ekstensi PHP yang dibutuhkan di lingkungan produksi
# TAMBAHKAN 'linux-headers' DI SINI
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev linux-headers \
    && docker-php-ext-install pdo pdo_mysql sockets gd

# Salin file aplikasi DAN folder vendor dari tahap 'builder'
COPY --from=builder /app .

# Atur kepemilikan file agar web server bisa menulis ke folder storage dan bootstrap/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port yang digunakan oleh PHP-FPM
EXPOSE 9000

# Perintah untuk menjalankan PHP-FPM saat kontainer dimulai
CMD ["php-fpm"]