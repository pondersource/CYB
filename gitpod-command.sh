#!/usr/bin/env bash
set -e

cd cyb-app
docker compose --file docker-compose.gitpod.yaml build
docker compose --file docker-compose.gitpod.yaml pull
docker compose --file docker-compose.gitpod.yaml up --detach
