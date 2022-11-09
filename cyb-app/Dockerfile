FROM bitnami/laravel:9.3.10

RUN apt update --yes

# TODO: install nodejs and install node packages too.

# php composer needs zip to unpack downloaded files.
RUN apt install --yes zip php-redis

COPY . /app

# remove development directories and lock files.
RUN rm -rf ./vendor composer.lock package-lock.json

# clean install packages.
RUN composer install
