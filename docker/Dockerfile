FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

RUN a2enmod rewrite


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Add Apache config to serve the public directory as DocumentRoot
COPY ./docker/apache.conf /etc/apache2/sites-available/000-default.conf


WORKDIR /var/www/html
COPY . .

EXPOSE 80
