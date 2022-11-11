#!/usr/bin/env bash

# set uid for cyb account, defaults to 1337
if [ -n "${WWWUSER}" ]; then
    usermod -u "${WWWUSER}" cyb
fi

# create composer directory.
if [ ! -d /.composer ]; then
    mkdir /.composer
fi

chmod -R ugo+rw /.composer

if [ $# -gt 0 ]; then
    exec gosu "${WWWUSER}" "$@"
else
    if [[ "${APP_ENTRY_MODE}" == "artisan" ]]; then
        # install packages.
        /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev
        # run database migrations prior to launching app.
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan migrate --force
        # optimize app sources.
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan optimize
        # serve application.
        exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=8000
    elif [[ "${APP_ENTRY_MODE}" == "horizon" ]]; then
        # wait until main container is fully up and running.
        sleep 10
        # publish horizon assets into /var/www/html/public/vendor/horizon .
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon:install --no-interaction --quiet
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon:publish --no-interaction --quiet
        # start horizon.
        exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon
    else
        # install packages.
        /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev
        # run database migrations prior to launching app.
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan migrate --force
        # optimize app sources.
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan optimize
        # start fpm.
        exec /etc/init.d/php8.1-fpm start
    fi
fi
