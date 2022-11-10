#!/usr/bin/env bash
set -e

cd cyb-app
docker compose --file docker-compose.gitpod.yaml build cyb
docker compose --file docker-compose.gitpod.yaml pull
