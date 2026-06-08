#!/usr/bin/env make
# STECI UK Parish — Docker shortcuts

.PHONY: help build up down logs shell migrate seed fresh prod dev

help:
	@echo "STECI UK Docker commands:"
	@echo "  make build     Build production images"
	@echo "  make up        Start production stack (nginx + app)"
	@echo "  make down      Stop all containers"
	@echo "  make logs      Tail container logs"
	@echo "  make shell     Open shell in app container"
	@echo "  make migrate   Run migrations"
	@echo "  make seed      Seed database"
	@echo "  make fresh     migrate:fresh --seed"
	@echo "  make prod      build + up (production)"
	@echo "  make dev       Start with dev overrides + vite"

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

fresh:
	docker compose exec app php artisan migrate:fresh --seed --force

prod: build up
	@echo "Site: http://localhost:$${NGINX_HTTP_PORT:-8080}"
	@echo "Admin: http://localhost:$${NGINX_HTTP_PORT:-8080}/admin"

dev:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml --profile dev up
