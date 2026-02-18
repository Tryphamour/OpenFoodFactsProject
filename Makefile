COMPOSE ?= docker compose
APP ?= frankenphp
MAIL ?= mailhog

DEFAULT_FILE ?= tests/Dashboard/UI/InternalDashboardApiTest.php

.PHONY: help up down down-v ps logs install migrate about test test-default test-file mailhog-url smoke

help:
	@echo Available targets:
	@echo   make up                Start or rebuild containers
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

up:
	$(COMPOSE) up -d --build

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
