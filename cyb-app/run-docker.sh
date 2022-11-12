#!/usr/bin/env bash

cd "$(dirname "${BASH_SOURCE[0]}")" || exit

if [[ "${1}" == "prod" ]]; then
    if [[ "${2}" == "build" ]]; then
        docker compose build
        docker compose pull
    elif [[ "${2}" == "up" ]]; then
        docker compose up --detach
    elif [[ "${2}" == "down" ]]; then
        docker compose down --remove-orphans
    fi
elif [[ "${1}" == "dev" ]]; then
    if [[ "${2}" == "build" ]]; then
        docker compose --file docker-compose.development.yaml build
        docker compose --file docker-compose.development.yaml pull
    elif [[ "${2}" == "up" ]]; then
        docker compose --file docker-compose.development.yaml up --detach
    elif [[ "${2}" == "down" ]]; then
        docker compose --file docker-compose.development.yaml down --remove-orphans
    fi
elif [[ "${1}" == "gitpod" ]]; then
    if [[ "${2}" == "build" ]]; then
        docker compose --file docker-compose.gitpod.yaml build
        docker compose --file docker-compose.gitpod.yaml pull
    elif [[ "${2}" == "up" ]]; then
        docker compose --file docker-compose.gitpod.yaml up --detach
    elif [[ "${2}" == "down" ]]; then
        docker compose --file docker-compose.gitpod.yaml down --remove-orphans
    fi
fi