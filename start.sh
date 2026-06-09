#!/usr/bin/env bash
set -e

if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# Use the PORT environment variable provided by Render, or default to 8080
export PORT=${PORT:-8080}

# Replace the ${PORT} variable in the Nginx configuration
envsubst '${PORT}' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf.tmp
mv /etc/nginx/conf.d/default.conf.tmp /etc/nginx/conf.d/default.conf

# Cache configuration, routes, and views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure permissions are correct
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Run migrations during deploy. Render free instances do not provide Shell access.
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
