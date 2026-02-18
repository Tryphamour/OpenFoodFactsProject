COMPOSE ?= docker compose
APP ?= frankenphp
MAIL ?= mailhog

.PHONY: up down down-v ps logs install migrate about test test-file mailhog-url

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

test-file:
ifndef FILE
	$(error usage: make test-file FILE=tests/Path/To/Test.php)
endif
	$(COMPOSE) exec -T -e APP_ENV=test $(APP) php vendor/bin/simple-phpunit $(FILE)

mailhog-url:
	$(COMPOSE) port $(MAIL) 8025
