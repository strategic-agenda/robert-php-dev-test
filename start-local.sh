#!/bin/bash

docker network create --driver=bridge --subnet=10.10.10.0/24 interview-backend || true
docker network create --driver=bridge --subnet=10.10.20.0/24 interview-frontend || true

cd ./_docker && docker compose -f docker-compose.yml up -d --build
cd ../api && docker compose -f docker-compose-local.yml up -d --build
cd ../www && docker compose -f docker-compose-local.yml up -d --build
