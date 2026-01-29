#!/bin/sh
set -e

# Run storage:link if symlink doesn't exist
if [ ! -L "/var/www/public/storage" ]; then
    php /var/www/artisan storage:link || true
fi

# Execute the passed command (e.g., php-fpm)
exec "$@"
