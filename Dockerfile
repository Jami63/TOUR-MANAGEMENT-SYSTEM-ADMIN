FROM php:8.3-apache

# Install required system packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    unzip \
    zip \
    && docker-php-ext-install pdo_mysql pdo_pgsql bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install official Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=10000

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy project files
COPY . .

# Install composer dependencies without executing scripts during build
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Ensure storage directories exist with proper permissions
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Setup entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
