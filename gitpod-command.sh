#!/usr/bin/env bash
set -e

cd cyb-app
docker compose build
docker compose pull
docker compose --file docker-compose.yaml up --detach
