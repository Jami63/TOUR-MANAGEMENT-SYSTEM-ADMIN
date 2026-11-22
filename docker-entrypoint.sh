#!/bin/sh
set -e

# Default PORT to 10000 if not set (Render standard)
PORT="${PORT:-10000}"

# Configure Apache port dynamically
sed -i "s/Listen [0-9]*/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Route logs to stderr for cloud container observability
sed -i "s|LOG_CHANNEL=.*|LOG_CHANNEL=stderr|g" /var/www/html/.env

# Setup Database: Use PostgreSQL if DATABASE_URL is set, else fallback to SQLite
if [ -n "$DATABASE_URL" ]; then
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=pgsql|g" /var/www/html/.env
else
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=sqlite|g" /var/www/html/.env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=/var/www/html/database/database.sqlite|g" /var/www/html/.env
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage directories and log file exist with full permissions
mkdir -p /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

touch /var/www/html/storage/logs/laravel.log

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Generate application key if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi

# Run package discovery
php artisan package:discover --ansi || true

# Run database migrations
php artisan migrate --force || true

# Cache configurations and routes for maximum performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
