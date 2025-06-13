FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy Apache config to serve from /var/www/html/public
COPY ./docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN apt-get update && apt-get install -y postgresql-client

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/

# Copy application files
COPY . .

# Make sure uploads directory is writable by Apache
RUN chown -R www-data:www-data /var/www/html/uploads

RUN composer require psr/http-server-middleware

# Copy local php.ini into container's PHP config directory
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Install Composer and PHP_CodeSniffer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer global require "squizlabs/php_codesniffer=*"
ENV PATH="$PATH:/root/.composer/vendor/bin"



EXPOSE 80
