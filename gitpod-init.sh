#!/bin/bash
set -e

cd cyb-app
docker compose build cyb
docker compose pull
