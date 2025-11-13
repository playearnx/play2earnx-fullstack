FROM php:8.2-apache

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Salin source code backend ke dalam container
COPY backend/ /var/www/html/

# Konfigurasi Apache agar mendukung routing
RUN echo "<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
DirectoryIndex index.php index.html\n" \
> /etc/apache2/conf-enabled/play2earnx.conf

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

WORKDIR /var/www/html
EXPOSE 80