# Gunakan base image yang lebih stabil dan siap produksi
FROM thecodingmachine/php:8.3-v4-apache

# Salin file konfigurasi opcache yang sudah kita buat
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Salin semua kode aplikasi ke dalam direktori kerja
COPY . .

# Atur kepemilikan file agar server bisa menulis ke folder yang dibutuhkan
# Perhatikan: Nama user di image ini adalah 'docker' bukan 'www-data'
RUN chown -R docker:docker /var/www/html/storage /var/www/html/bootstrap/cache

# Script startup akan dijalankan secara otomatis oleh base image ini
# Kita hanya perlu memastikan cache config dihapus dan migrasi dijalankan
# Tambahkan perintah ini ke file composer.json di bagian "scripts" -> "post-install-cmd" jika belum ada
# RUN composer install --no-dev --optimize-autoloader
# RUN php artisan config:clear
# RUN php artisan migrate --force