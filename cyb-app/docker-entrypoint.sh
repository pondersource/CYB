#!/usr/bin/env bash

cd /app || exit

# clean install packages.
RUN composer install --no-interaction --optimize-autoloader --no-dev

# php horizon create assets.
RUN php artisan horizon:install --no-interaction
RUN php artisan horizon:publish --no-interaction

php artisan migrate --force
php artisan optimize
php artisan serv --host=0.0.0.0
