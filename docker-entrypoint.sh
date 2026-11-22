#!/bin/sh
set -e

# Default PORT to 10000 if not set (Render standard)
PORT="${PORT:-10000}"

# Configure Apache port dynamically
sed -i "s/Listen [0-9]*/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Setup SQLite fallback if no external DB configured
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DATABASE_URL" -a -z "$DB_HOST" ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

# Generate application key if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations
php artisan migrate --force || true

# Cache configurations and routes for maximum performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
