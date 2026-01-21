#!/bin/sh
set -e

# Grant write permissions to the var folder so that Symfony can create a cache
# -R makes this recursive
mkdir -p var/cache var/log
chmod -R 777 var/

composer install --no-interaction --optimize-autoloader

exec "$@"