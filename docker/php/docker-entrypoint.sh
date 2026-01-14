#!/bin/sh
set -e

# Grant write permissions to the var folder so that Symfony can create a cache
# -R makes this recursive
mkdir -p var/cache var/log
chmod -R 777 var/

# Install dependencies if the vendor folder does not yet exist or is empty
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
    echo "--- Installing Symfony dependencies ---"
    composer install --no-interaction --optimize-autoloader
fi

exec "$@"