#!/usr/bin/env bash

# exit on first error/non-zero error code returning command
set -e
# print trace of commands
set -x

cd /app || exit

# clean install packages.
composer install --no-interaction --optimize-autoloader --no-dev

# run database migrations prior to launching app.
php artisan migrate --force

# php horizon create assets.
php artisan horizon:install --no-interaction  --quiet
php artisan horizon:publish --no-interaction  --quiet

# run horizon via supervisor.
# nohup and the end of line & will push supervisord to background
nohup supervisord &

# wait until supervisor is ready.
sleep 2

# run app.
# TODO: should we read $PORT environment variable and pass it into artisan serv via --port ?
php artisan optimize
php artisan serv --host=0.0.0.0
