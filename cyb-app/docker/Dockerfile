FROM ubuntu:22.04

# keys for oci taken from:
# https://github.com/opencontainers/image-spec/blob/main/annotations.md#pre-defined-annotation-keys
LABEL org.opencontainers.image.licenses=MIT
LABEL org.opencontainers.image.title="Connect Your Books"
LABEL org.opencontainers.image.source=https://github.com/pondersource/CYB
LABEL org.opencontainers.image.authors="Mohammad Mahdi Baghbani Pourvahid"

# set environment variables and timezone.
ARG CYBGID=1337
ARG CYBUID=1337
ARG NODE_VERSION=16
ARG POSTGRES_VERSION=14
ARG APACHE_RUN_USER=cyb
ARG APACHE_RUN_GROUP=www-data

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln --symbolic --no-dereference --force /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# create cyb group and user as www-data owner (www-data is usually uid=33).
# give cyb uid 1337 that can be altred through environment variables through entry point.
# we have to give ownership to www-data so that Nginx can create cache and update Laravel log files.
RUN groupadd --force --gid $CYBGID cyb
RUN useradd --create-home --shell /bin/bash --no-user-group --gid $CYBGID --uid $CYBUID cyb
RUN usermod --append --groups www-data cyb

# app directory.
WORKDIR /var/www/html

RUN apt update --yes

# install dependencies.
RUN apt install --yes   \
    git                 \
    zip                 \
    curl                \
    gosu                \
    nginx               \
    gnupg               \
    unzip               \
    sqlite3             \
    python2             \
    supervisor          \
    libpng-dev          \
    libcap2-bin         \
    ca-certificates

# setup keys and add ppa keys to ubuntu.
RUN mkdir --parent ~/.gnupg
RUN chmod 600 ~/.gnupg
RUN echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf
RUN echo "keyserver hkp://keyserver.ubuntu.com:80" >> ~/.gnupg/dirmngr.conf
RUN gpg --recv-key 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c
RUN gpg --export 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c > /usr/share/keyrings/ppa_ondrej_php.gpg

# add ondrej repo for installing php in ubuntu.
RUN echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list

# update apt repo cache and install php.
RUN apt update --yes
RUN apt install --yes         \
    apache2                   \
    php8.1-gd                 \
    php8.1-cli                \
    php8.1-dev                \
    php8.1-xml                \
    php8.1-zip                \
    php8.1-fpm                \
    php8.1-curl               \
    php8.1-imap               \
    php8.1-soap               \
    php8.1-intl               \
    php8.1-pcov               \
    php8.1-ldap               \
    php8.1-mysql              \
    php8.1-pgsql              \
    php8.1-redis              \
    php8.1-bcmath             \
    php8.1-xdebug             \
    php8.1-sqlite3            \
    php8.1-msgpack            \
    php8.1-mbstring           \
    php8.1-igbinary           \
    php8.1-readline           \
    php8.1-memcached          \
    libapache2-mod-php8.1

# install composer.
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

# install nodejs and npm and yarn.
RUN curl --silent --show-error --location https://deb.nodesource.com/setup_$NODE_VERSION.x | bash -
RUN apt install --yes nodejs
RUN npm install --global npm

RUN curl --silent --show-error --location https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarn.gpg >/dev/null
RUN echo "deb [signed-by=/usr/share/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list
RUN apt  install --yes yarn

# install postgresql client and mysql client.
RUN curl --silent --show-error --location https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/pgdg.gpg >/dev/null
RUN echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt jammy-pgdg main" > /etc/apt/sources.list.d/pgdg.list
RUN apt update --yes
RUN apt install --yes mysql-client
RUN apt install --yes postgresql-client-$POSTGRES_VERSION

# cleanup.
RUN apt --yes autoremove
RUN apt clean
RUN rm --recursive --force /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.1

RUN mkdir --parent /cyb-src

COPY php.ini                /etc/php/8.1/cli/conf.d/99-cyb.ini
COPY envvars.env            /etc/apache2/envvars
COPY entrypoint.sh          /usr/local/bin/entrypoint.sh
COPY cyb-nginx.conf         /etc/nginx/nginx.conf
COPY healthcheck.js         /cyb-src/healthcheck.js
COPY cyb-apache.conf        /etc/apache2/sites-available/cyb-apache.conf
COPY cyb-php-fpm.conf       /etc/php/8.1/fpm/pool.d/cyb.conf
COPY cyb-apache-port.conf   /etc/apache2/ports.conf

# remove default configuration files.
RUN rm /etc/php/8.1/fpm/pool.d/www.conf

# disbale default apache site.
RUN a2dissite 000-default.conf

# stop all services.
RUN service php8.1-fpm stop
RUN service apache2 stop
RUN service nginx stop

# change owner of apache directories.
RUN chown cyb:www-data /var/log/apache2

RUN chmod +x /usr/local/bin/entrypoint.sh

ARG BUILD_REPO="https://github.com/pondersource/CYB/archive/refs/heads"
ARG BUILD_BRANCH="main"

# use cache buster to force download source code.
ARG CACHE_BUSTER
RUN echo "command with external dependencies, cache buster value is ${CACHE_BUSTER}"
ARG DOWNLOAD_LOCATION="${BUILD_REPO}/${BUILD_BRANCH}.zip"

# get cyb project from git repository.
RUN curl --fail --silent --show-error --location "${DOWNLOAD_LOCATION}" --output /cyb-src/cyb.zip
RUN unzip -qq /cyb-src/cyb.zip -d /cyb-src
RUN mv /cyb-src/CYB* /cyb-src/cyb

# set volume for mounting.
VOLUME /var/www/html

ENTRYPOINT ["entrypoint.sh"]
