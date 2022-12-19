#!/usr/bin/env bash

cd "$(dirname "${BASH_SOURCE[0]}")" || exit

if [[ "${1}" == "prod" ]]; then
    if [[ "${2}" == "pull" ]]; then
        docker compose pull
    elif [[ "${2}" == "build" ]]; then
        docker compose build
    elif [[ "${2}" == "up" ]]; then
        docker compose up --no-build --detach
    elif [[ "${2}" == "down" ]]; then
        docker compose down
    fi
elif [[ "${1}" == "dev" ]]; then
    if [[ "${2}" == "pull" ]]; then
        docker compose --file docker-compose.development.yaml pull
    elif [[ "${2}" == "build" ]]; then
        docker compose --file docker-compose.development.yaml build
    elif [[ "${2}" == "up" ]]; then
        docker compose --file docker-compose.development.yaml up --no-build --detach
    elif [[ "${2}" == "down" ]]; then
        docker compose --file docker-compose.development.yaml down
    fi
elif [[ "${1}" == "gitpod" ]]; then
    BRANCH=$(git rev-parse --abbrev-ref HEAD)
    if [[ "${2}" == "pull" ]]; then
        IMAGE_LABEL=$BRANCH docker compose --file docker-compose.gitpod.yaml pull
    elif [[ "${2}" == "build" ]]; then
        IMAGE_LABEL=BRANCH docker compose --file docker-compose.gitpod.yaml build
    elif [[ "${2}" == "up" ]]; then
        IMAGE_LABEL=BRANCH docker compose --file docker-compose.gitpod.yaml up --no-build --detach
    elif [[ "${2}" == "down" ]]; then
        IMAGE_LABEL=BRANCH docker compose --file docker-compose.gitpod.yaml down
    fi
fi
