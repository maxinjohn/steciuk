#!/usr/bin/env make
# STECI UK Parish — Docker & artisan shortcuts

.PHONY: help build up down logs shell migrate seed bootstrap sync fresh prod dev

help:
	@echo "STECI UK commands:"
	@echo "  make build      Build production Docker images"
	@echo "  make up         Start single app container"
	@echo "  make down       Stop all containers"
	@echo "  make logs       Tail container logs"
	@echo "  make shell      Open shell in app container"
	@echo "  make migrate    Run migrations in container"
	@echo "  make seed       Run db:seed (respects SEED_MODE in .env)"
	@echo "  make bootstrap  First-time reference data install"
	@echo "  make sync       Sync dev reference data without wiping prod data"
	@echo "  make fresh      migrate:fresh + bootstrap"
	@echo "  make prod       build + up (production)"
	@echo "  make dev        Dev mode with source bind mount"

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

seed:
	docker compose exec app php artisan db:seed --force

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

dev:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml up
