#!/bin/bash
set -e
docker build -t lara-test .
docker run -d --network=testnet --name cyb-redis redis:7.0.4-bullseye
docker run -d --network=testnet --name cyb-mariadb \
  -e "MYSQL_RANDOM_ROOT_PASSWORD=yes" \
  -e "MYSQL_DATABASE=cyb_db" \
  -e "MYSQL_USER=cyb" \
  -e "MYSQL_PASSWORD=simpleYetNotSecure" \
  mariadb:10.9.2-jammy

function waitForMysql {
  x=$(docker exec -it $1 ss -tulpn | grep 3306 | wc -l)
  until [ $x -ne 0 ]
  do
    echo Waiting for $1 to start, this usually takes about 10 seconds ... $x
    sleep 1
    x=$(docker exec -it $1 ss -tulpn | grep 3306 | wc -l)
  done
  echo $1 port is open
}
waitForMysql cyb-mariadb

docker run -it --network=testnet \
  -e "DB_CONNECTION=mysql" \
  -e "DB_HOST=cyb-mariadb" \
  -e "DB_DATABASE=cyb_db" \
  -e "DB_USERNAME=cyb" \
  -e "DB_PASSWORD=simpleYetNotSecure" \
  -e "REDIS_HOST=cyb-redis" \
  -e "REDIS_PASSWORD=null" \
  -e "REDIS_PORT=6379" \
  lara-test php artisan migrate --force

docker run -it --network=testnet \
  -e "DB_CONNECTION=mysql" \
  -e "DB_HOST=cyb-mariadb" \
  -e "DB_DATABASE=cyb_db" \
  -e "DB_USERNAME=cyb" \
  -e "DB_PASSWORD=simpleYetNotSecure" \
  -e "REDIS_HOST=cyb-redis" \
  -e "REDIS_PASSWORD=null" \
  -e "REDIS_PORT=6379" \
  lara-test php artisan optimize

# docker exec -it cyb-mariadb mysql -u cyb -psimpleYetNotSecure -e "CREATE TABLE cyb"
docker run -d --network=testnet -p 8000:8000 --name cyb \
  -e "DB_CONNECTION=mysql" \
  -e "DB_HOST=cyb-mariadb" \
  -e "DB_DATABASE=cyb_db" \
  -e "DB_USERNAME=cyb" \
  -e "DB_PASSWORD=simpleYetNotSecure" \
  -e "REDIS_HOST=cyb-redis" \
  -e "REDIS_PASSWORD=null" \
  -e "REDIS_PORT=6379" \
  -e "APP_NAME=Laravel" \
  -e "APP_ENV=local" \
  -e "APP_KEY=base64:1IKnsyc2Om+mWPqSKr44xdSTkzFQNS3xR9IRsX0ovG8=" \
  -e "APP_DEBUG=true" \
  lara-test sleep 30000

echo Now: docker exec -it cyb php artisan serv --host=0.0.0.0

