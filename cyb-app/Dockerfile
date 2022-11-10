FROM bitnami/laravel:9.3.10

RUN apt update --yes

# TODO: install nodejs and install node packages too.
# php composer needs zip to unpack downloaded files.
RUN apt install --yes lsb-release ca-certificates apt-transport-https software-properties-common gnupg2
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
RUN curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg

RUN apt update
RUN apt install --yes zip php8.1-redis

COPY . /app

# remove development directories and lock files.
RUN rm -rf ./vendor composer.lock package-lock.json .env

# clean install packages.
RUN composer install
