#!/usr/bin/env bash

cd /app || exit

php artisan migrate --force
php artisan optimize
php artisan serv --host=0.0.0.0
