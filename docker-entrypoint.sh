#!/bin/sh
set -e

# Jalankan storage:link jika belum ada symlink
if [ ! -L "/var/www/public/storage" ]; then
    php /var/www/artisan storage:link || true
fi

exec "$@"