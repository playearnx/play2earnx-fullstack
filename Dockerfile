FROM php:8.1-apache
# Install extensions and composer
RUN apt-get update && apt-get install -y libzip-dev unzip git && docker-php-ext-install pdo pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
# Enable apache rewrite
RUN a2enmod rewrite
COPY backend/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
RUN composer install --no-dev || true
EXPOSE 80
CMD ["apache2-foreground"]
