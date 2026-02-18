COMPOSE ?= docker compose
APP ?= frankenphp
MAIL ?= mailhog
DEMO_COMPOSE ?= docker compose -f compose.yaml -f compose.override.yaml -f compose.demo.yaml

DEFAULT_FILE ?= tests/Dashboard/UI/InternalDashboardApiTest.php

.PHONY: help up demo-up down down-v ps logs install migrate about test test-default test-file mailhog-url smoke demo-about demo-smoke demo-mailhog-url

help:
	@echo Available targets:
	@echo   make up                Start or rebuild containers
	@echo   make demo-up           Start optimized demo profile (prod-like)
	@echo   make down              Stop containers
	@echo   make down-v            Stop containers and remove volumes (destructive)
	@echo   make ps                Show container status
	@echo   make logs              Tail frankenphp logs
	@echo   make install           Run composer install in app container
	@echo   make migrate           Run doctrine migrations
	@echo   make about             Run Symfony diagnostics
	@echo   make test              Run full test suite \(APP_ENV=test\)
	@echo   make test-default      Run default targeted test file
	@echo   make test-file FILE=... Run specific test file
	@echo   make mailhog-url       Print Mailhog host:port
	@echo   make smoke             Quick runtime smoke checks \(ps + about\)
	@echo   make demo-about        Symfony diagnostics in demo profile
	@echo   make demo-smoke        Quick runtime smoke checks in demo profile
	@echo   make demo-mailhog-url  Print Mailhog host:port in demo profile

up:
	$(COMPOSE) up -d --build

demo-up:
	$(DEMO_COMPOSE) up -d --build

down:
	$(COMPOSE) down

down-v:
	$(COMPOSE) down -v

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs --tail=200 -f $(APP)

install:
	$(COMPOSE) exec -T $(APP) composer install

migrate:
	$(COMPOSE) exec -T $(APP) php bin/console doctrine:migrations:migrate --no-interaction

about:
	$(COMPOSE) exec -T $(APP) php bin/console about

test:
	$(COMPOSE) exec -T -e APP_ENV=test $(APP) php vendor/bin/simple-phpunit

test-default:
	$(COMPOSE) exec -T -e APP_ENV=test $(APP) php vendor/bin/simple-phpunit $(DEFAULT_FILE)

test-file:
ifndef FILE
	$(error usage: make test-file FILE=tests/Path/To/Test.php)
endif
	$(COMPOSE) exec -T -e APP_ENV=test $(APP) php vendor/bin/simple-phpunit $(FILE)

mailhog-url:
	$(COMPOSE) port $(MAIL) 8025

smoke: ps about

demo-about:
	$(DEMO_COMPOSE) exec -T $(APP) php bin/console about

demo-smoke:
	$(DEMO_COMPOSE) ps
	$(DEMO_COMPOSE) exec -T $(APP) php bin/console about

demo-mailhog-url:
	$(DEMO_COMPOSE) port $(MAIL) 8025
