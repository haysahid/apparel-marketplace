#!/bin/sh
set -e

# Sync public assets from the image's backup to the mounted volume
# using rsync to only update changed files
if [ -d "/var/www/public_backup" ]; then
    echo "Syncing public assets to volume..."
    rsync -ar /var/www/public_backup/ /var/www/public/
fi

# Run storage:link if symlink doesn't exist
if [ ! -L "/var/www/public/storage" ]; then
    php /var/www/artisan storage:link || true
fi

# Execute the passed command (e.g., php-fpm)
exec "$@"
