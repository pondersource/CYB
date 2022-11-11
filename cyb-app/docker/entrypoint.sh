#!/usr/bin/env bash

# set uid for cyb account, defaults to 1337
if [ -n "${CYBUID}" ]; then
    usermod --uid "${CYBUID}" cyb
fi

# create composer directory.
if [ ! -d /.composer ]; then
    mkdir /.composer
fi

chmod --recursive ugo+rw /.composer

# populate /var/www/html with cyb app.
cp --archive --recursive --no-clobber ~/CYB-dockerization/cyb-app/. /var/www/html

# install packages.
/usr/bin/composer install --no-interaction --optimize-autoloader --no-dev

# publish horizon assets into /var/www/html/public/vendor/horizon .
/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon:install --no-interaction --quiet
/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon:publish --no-interaction --quiet

# set file ownership.
chown --recursive cyb:www-data /var/www/html
find /var/www/html -type f -exec chmod 644 {} \;
find /var/www/html -type d -exec chmod 755 {} \;
chgrp --recursive www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod --recursive ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

# optimize app sources.
/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan optimize

if [ $# -gt 0 ]; then
    exec gosu "${CYBUID}" "$@"
else
    # run database migrations prior to launching app.
    /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan migrate --force

    # start horizon.
    nohup /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon &
    # wait until horizon is fully up and running.
    sleep 5

    if [[ "${APP_ENTRY_MODE}" == "artisan" ]]; then
        # serve application.
        exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=8000
    elif [[ "${APP_ENTRY_MODE}" == "apache" ]]; then
        # enable cyb website.
        a2ensite cyb-apache
        service apache2 reload
        # enable rewrite module.
        a2enmod rewrite
        # enable apache php executor.
        a2enmod php8.1
        # start apache2 and bring it to foreground.
        exec apachectl -DFOREGROUND
    else
        # start fpm.
        service php8.1-fpm reload
        service php8.1-fpm restart
        service php8.1-fpm status
    fi
fi
