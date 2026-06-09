#!/usr/bin/env make
# STECI UK Parish — Docker shortcuts (single compose file)

.PHONY: help build up down logs shell migrate bootstrap sync fresh prod deploy deploy-sync verify docker-smoke

help:
	@echo "STECI UK — docker compose (dev/prod via .env only):"
	@echo "  make build       Build image"
	@echo "  make up          Start container"
	@echo "  make down        Stop container"
	@echo "  make logs        Tail logs"
	@echo "  make shell       Shell into container"
	@echo "  make migrate     Run migrations"
	@echo "  make bootstrap   First-time reference data"
	@echo "  make sync        Sync reference data to prod safely"
	@echo "  make fresh       migrate:fresh + bootstrap"
	@echo "  make prod        build + up (local)"
	@echo "  make deploy      Production deploy (git pull + docker + migrate)"
	@echo "  make deploy-sync Deploy + sync reference data from seeders"

build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

shell:
	docker compose exec app sh

migrate:
	docker compose exec app php artisan migrate --force

bootstrap:
	docker compose exec app php artisan site:bootstrap --force

sync:
	docker compose exec app php artisan site:sync-reference-data --force

fresh:
	docker compose exec app php artisan migrate:fresh --force
	docker compose exec app php artisan site:bootstrap --force

prod: build up
	@echo "Site:  http://localhost:$${NGINX_HTTP_PORT:-8080}"
	@echo "Admin: http://localhost:$${NGINX_HTTP_PORT:-8080}/admin"

deploy:
	./scripts/deploy.sh

deploy-sync:
	./scripts/deploy.sh --sync

verify:
	php artisan test

docker-smoke:
	sh scripts/docker-smoke.sh
