#!/bin/bash
set -e

cd cyb-app
docker compose up -d
docker exec -it cyb php artisan horizon