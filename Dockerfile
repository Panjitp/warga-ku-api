# Gunakan image resmi dari Docker yang sudah ada PHP 8.3 + Server Apache
FROM php:8.3-apache

# Instal semua library sistem yang dibutuhkan sekaligus
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Instal semua ekstensi PHP yang dibutuhkan
RUN docker-php-ext-install pdo pdo_mysql gd zip sockets

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur direktori kerja utama
WORKDIR /var/www/html

# Salin semua file proyek Anda
COPY . .

# Instal semua dependensi dari composer.json
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Atur kepemilikan file agar Laravel bisa menulis
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Konfigurasi Apache untuk mengarah ke folder /public Laravel
RUN a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# === SKRIP PERBAIKAN OTOMATIS FINAL ===
# Membuat skrip startup yang akan:
# 1. Membuat file .env dari variabel Railway (memastikan kutip untuk MAIL_FROM_NAME)
# 2. Menghapus cache konfigurasi yang usang
# 3. Menjalankan migrasi database
# 4. Memulai server web Apache
RUN echo '#!/bin/sh' > /entrypoint.sh && \
    echo 'printenv | sed -e "s/^\(MAIL_FROM_NAME=.*\)\s\(.*\)$/\1 \"\2\"/" > .env' >> /entrypoint.sh && \
    echo 'php artisan config:clear' >> /entrypoint.sh && \
    echo 'php artisan migrate --force' >> /entrypoint.sh && \
    echo 'apache2-foreground' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

# Jalankan skrip startup yang sudah kita buat
CMD ["/entrypoint.sh"]