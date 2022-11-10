FROM bitnami/laravel:9.3.10

RUN apt update --yes

# TODO: install nodejs and install node packages too.
# php composer needs zip to unpack downloaded files.
RUN apt install --yes zip

# copy project into image.
COPY . /app

# clean install packages.
RUN composer install

# make sure its excutable.
RUN chmod +x /app/docker-entrypoint.sh

ENTRYPOINT ["/app/docker-entrypoint.sh"]
