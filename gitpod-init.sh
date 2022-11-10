#!/bin/bash
set -e

cd cyb-app
docker compose pull
docker compose build
