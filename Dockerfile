# Gunakan image resmi dari Docker yang sudah ada PHP 8.3 + Server Apache
FROM php:8.3-apache

# Instal semua library sistem yang dibutuhkan
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Instal ekstensi-ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql gd zip sockets

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Atur kepemilikan file
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Konfigurasi Apache untuk mengarah ke folder /public Laravel
RUN a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# HANYA JALANKAN SERVER, TANPA PERINTAH LAIN
CMD ["apache2-foreground"]