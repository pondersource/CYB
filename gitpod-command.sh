#!/usr/bin/env bash
set -e

cd cyb-app
docker compose --file docker-compose.gitpod.yaml up --detach
