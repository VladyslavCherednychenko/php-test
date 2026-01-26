#!/bin/sh
set -e

# Grant write permissions to the var folder so that Symfony can create a cache
# -R makes this recursive
mkdir -p var/cache var/log
chmod -R 777 var/

echo "composer install"
composer install --no-interaction --optimize-autoloader

if [ ! -f /var/www/html/config/jwt/private.pem ]; then
  echo "lexik:jwt:generate-keypair"
  php bin/console lexik:jwt:generate-keypair --no-interaction --overwrite
fi

echo "doctrine:database:create"
php bin/console doctrine:database:create --no-interaction --if-not-exists

echo "doctrine:migrations:migrate"
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"