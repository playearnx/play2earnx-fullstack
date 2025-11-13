# Gunakan PHP + Apache
FROM php:8.2-apache

# Install ekstensi PHP yang diperlukan
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Aktifkan mod_rewrite agar routing PHP berfungsi
RUN a2enmod rewrite

# Salin file backend ke direktori web Apache
COPY backend/ /var/www/html/

# Pastikan hak akses benar
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Set working directory
WORKDIR /var/www/html

# Tambahkan konfigurasi Apache agar index.php dikenali
RUN echo "<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
DirectoryIndex index.php index.html\n" \
> /etc/apache2/conf-enabled/play2earnx.conf

# Expose port 80
EXPOSE 80
