#!/usr/bin/env bash

# set uid for cyb account, defaults to 1337
if [ -n "${CYBUID}" ]; then
    usermod --uid "${CYBUID}" cyb
fi

if [[ "${APP_CONTAINER_ROLE}" == "horizon" ]]; then
    if [ $# -gt 0 ]; then
        exec gosu "${CYBUID}" "$@"
    else
        # start horizon.
        exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon
    fi
elif [[ "${APP_CONTAINER_ROLE}" == "scheduler" ]]; then
    if [ $# -gt 0 ]; then
        exec gosu "${CYBUID}" "$@"
    else
        # run scheduler task every minute.
        while true; do
            /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan schedule:run --verbose --no-interaction
            sleep 60
        done
    fi
else
    # create composer directory if it doesn't exist.
    if [ ! -d /.composer ]; then
        mkdir /.composer
    fi

    chmod --recursive ugo+rw /.composer

    # create composer directory if it doesn't exist.
    if [ ! -d /var/www/html ]; then
        mkdir --parent /var/www/html
    fi

    # if /var/www/html has any files in it, do not copy cyb src into it.
    if [ -n "$(find /var/www/html -prune -empty -type d 2>/dev/null)" ]; then
        echo "/var/www/html is an empty directory, populating it with cyb app."
        # populate /var/www/html with cyb app.
        cp --archive --recursive --no-clobber /cyb-src/cyb/cyb-app/. /var/www/html
    else
        echo "/var/www/html contains files, doing noting."
    fi

    # install packages.
    /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev

    # publish horizon assets into /var/www/html/public/vendor/horizon .
    /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon:install --no-interaction --quiet
    /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan horizon:publish --no-interaction --quiet

    # set file ownership and permissions.
    chown --recursive cyb:www-data /var/www/html
    find /var/www/html -type f -exec chmod 644 {} \;
    find /var/www/html -type d -exec chmod 755 {} \;
    chgrp --recursive www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod --recursive ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

    # only optimize sources when we are in productions, otherwise clear all optimizations.
    if [[ "${CYB_APP_MODE}" == "development" ]]; then
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan optimize:clear
    else
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan optimize
    fi

    if [ $# -gt 0 ]; then
        exec gosu "${CYBUID}" "$@"
    else
        # run database migrations prior to launching app.
        /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan migrate --force

        if [[ "${APP_WEBSERVER_TYPE}" == "artisan" ]]; then
            # serve application.
            exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=8000
        elif [[ "${APP_WEBSERVER_TYPE}" == "apache" ]]; then
            # enable cyb website.
            a2ensite cyb-apache
            service apache2 reload
            # enable rewrite module.
            a2enmod rewrite
            # enable apache php executor.
            a2enmod php8.1
            # show logs in terminal.
            ln --symbolic --force /dev/stderr /var/log/apache2/error.log
            ln --symbolic --force /dev/stdout /var/log/apache2/access.log
            # start apache2 and bring it to foreground.
            exec apachectl -DFOREGROUND
        else
            # start fpm.
            service php8.1-fpm reload
            service php8.1-fpm restart
            service php8.1-fpm status
            # check nginx status.
            service nginx status
            # show logs in terminal.
            ln --symbolic --force /dev/stderr /var/log/nginx/error.log
            ln --symbolic --force /dev/stdout /var/log/nginx/access.log
            # start nginx.
            nginx
        fi
    fi
fi
