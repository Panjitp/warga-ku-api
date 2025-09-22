# Tahap 1: Builder - Menginstal dependensi PHP
FROM composer:2.7 as builder

WORKDIR /app

# Instal ekstensi yang dibutuhkan
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev linux-headers \
    && docker-php-ext-install gd zip sockets pdo pdo_mysql

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader
COPY . .

# ---------------------------------------------------------------------

# Tahap 2: Final - Menggabungkan Nginx dan PHP-FPM
FROM php:8.3-fpm-alpine

# Instal Nginx
RUN apk add --no-cache nginx

# Salin file konfigurasi Nginx yang sudah kita buat
COPY nginx.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www/html

# Salin file aplikasi dari tahap 'builder'
COPY --from=builder /app .

# Atur kepemilikan file
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Buat file script untuk menjalankan kedua layanan
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php-fpm &' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh && \
    chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]