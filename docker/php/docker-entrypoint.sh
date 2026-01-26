#!/bin/sh
set -e

# Grant write permissions to the var folder so that Symfony can create a cache
# -R makes this recursive
mkdir -p var/cache var/log
chmod -R 777 var/

composer install --no-interaction --optimize-autoloader

php bin/console doctrine:database:create --no-interaction --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"