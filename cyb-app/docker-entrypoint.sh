#!/usr/bin/env bash

cd /app || exit

# clean install packages.
composer install --no-interaction --optimize-autoloader --no-dev

# php horizon create assets.
php artisan horizon:install --no-interaction
php artisan horizon:publish --no-interaction

php artisan migrate --force
php artisan optimize
php artisan serv --host=0.0.0.0
