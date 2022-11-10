#!/bin/bash
set -e

cd cyb-app
docker compose build cyb
docker compose pull
docker compose run --rm --entrypoint composer cyb install
