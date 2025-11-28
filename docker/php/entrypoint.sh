#!/bin/bash

# Exit on error
set -e

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link
fi

# Execute the main command (php-fpm)
echo "Starting PHP-FPM..."
exec "$@"
