FROM bitnami/laravel:9.3.10

RUN apt update --yes

# TODO: install nodejs and install node packages too.
# php composer needs zip to unpack downloaded files.
RUN apt install --yes zip
RUN apt install --yes supervisor

# copy project into image.
COPY . /app

# clean install packages.
RUN composer install --no-interaction --optimize-autoloader --no-dev

# configure supervisor to run horizon.
RUN cp ./supervisord.conf /etc/supervisor/supervisord.conf
RUN cp ./horizon.conf /etc/supervisor/conf.d/horizon.conf

# php horizon create assets.
RUN php artisan horizon:install --no-interaction
RUN php artisan horizon:publish --no-interaction

# optimize sources.
RUN php artisan optimize

# make sure its excutable.
RUN chmod +x /app/docker-entrypoint.sh

ENTRYPOINT ["/app/docker-entrypoint.sh"]
