include .env
export

APP_NETWORK = rightmart_default

# Frequently used commands:
COMPOSE = docker compose -p rightmart-test -f docker/docker-compose.yaml
BUILDER = $(COMPOSE) run --rm php-builder
CLI = $(COMPOSE) exec -t php-cli

## Build/Destroy:
setup:
	docker network create $(APP_NETWORK);
	make build
	make composer-install
	$(BUILDER) bash -c "chmod +x ./docker/setup.sh && ./docker/setup.sh"
	make up
	$(BUILDER) ./docker/wait_for_elastic.sh
	$(CLI) bin/console app:setup-db
teardown:
	-$(COMPOSE) down --volumes
	-docker network rm $(APP_NETWORK);
	-rm -rf ./data
	-rm -rf ./vendor
	-rm -rf ./var/*
build:
	$(COMPOSE) build
composer-install:
	$(BUILDER) composer install

## Utilities:
up:
	$(COMPOSE) up -d
down:
	$(COMPOSE) down
ingest:
	$(CLI) php bin/console app:ingest-logs $(LOG_FILE)
ingest-no-tail:
	$(CLI) php bin/console app:ingest-logs $(LOG_FILE) --no-tail
reset-file-database:
	$(CLI) rm var/storage.json

## Tests:
test:
	$(COMPOSE) run --rm php-builder php bin/phpunit

## Helpers:
shell-builder:
	$(COMPOSE) run --rm -it php-builder bash
shell-cli:
	$(COMPOSE) exec -it php-cli sh
shell-fpm:
	$(COMPOSE) exec -it php-cli sh
shell-nginx:
	$(COMPOSE) exec -it nginx sh
compose-debug:
	$(COMPOSE) config
compose-ps:
	$(COMPOSE) ps
healthcheck-elastic:
	curl -X GET "localhost:9200"
healthcheck-logstash:
	echo '{"message":"hello world from Farzan"}' | nc localhost 5000
