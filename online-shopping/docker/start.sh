#!/bin/bash

# Exit on error
set -e

echo "Starting PurpleBug Online Shopping Application..."

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan db:show 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set correct permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g 'daemon off;'
