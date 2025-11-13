# Gunakan image PHP + Apache
FROM php:8.1-apache

# Install ekstensi yang dibutuhkan
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy source code ke dalam container
COPY backend/ /var/www/html/

# Ubah permission agar Apache bisa akses
RUN chown -R www-data:www-data /var/www/html

# Aktifkan mod_rewrite untuk routing PHP (jika pakai)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

