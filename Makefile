.PHONY: build up down restart test shell logs setup migrate

setup:
	docker compose build
	docker compose up -d
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate

build:
	docker compose build

migrate:
	docker compose exec app php artisan migrate

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

test:
	docker compose exec app php artisan test --filter XrayApiTest

shell:
	docker compose exec app bash

listen:
	docker compose exec app php artisan idn:control-plane:listen

logs:
	docker compose logs -f
