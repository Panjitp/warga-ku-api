# Gunakan image resmi dari Docker yang sudah ada PHP 8.3 + Server Apache
FROM php:8.3-apache

# Instal semua library sistem yang dibutuhkan untuk ekstensi PHP Laravel
# Ini dilakukan dalam satu langkah agar efisien
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Instal ekstensi-ekstensi PHP yang dibutuhkan oleh Laravel
RUN docker-php-ext-install pdo pdo_mysql gd zip sockets

# Instal Composer (manajer paket PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur direktori kerja utama
WORKDIR /var/www/html

# Salin semua file proyek Anda ke dalam container
COPY . .

# Instal semua dependensi dari composer.json
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Atur kepemilikan file agar Laravel bisa menulis ke folder storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Konfigurasi Apache untuk mengarah ke folder /public Laravel
RUN a2enmod rewrite \
    && echo "<Directory /var/www/html/public>" > /etc/apache2/conf-available/laravel.conf \
    && echo "    AllowOverride All" >> /etc/apache2/conf-available/laravel.conf \
    && echo "</Directory>" >> /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel