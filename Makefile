include .env
export

COMPOSE = docker compose -p rightmart-test -f docker/docker-compose.yaml
APP_NETWORK = rightmart_default

# Build/Destroy:
setup:
	docker network create $(APP_NETWORK);
	# @todo composer, database etc.
teardown:
	docker network rm $(APP_NETWORK);
	#$(DOCKER) stop
build:
	$(COMPOSE) build

# Utilities:
up:
	$(COMPOSE) up -d
down:
	$(COMPOSE) down
ingest:
	$(COMPOSE) exec -t php-cli php bin/console app:ingest-logs $(LOG_FILE)
ingest-no-tail:
	$(COMPOSE) exec -t php-cli php bin/console app:ingest-logs $(LOG_FILE) --no-tail
reset-file-database:
	$(COMPOSE) exec -t php-cli rm var/storage.json

# Helpers:
test:
	$(COMPOSE) run --rm php-builder php bin/phpunit
shell-builder:
	$(COMPOSE) run --rm -it php-builder bash
shell-cli:
	$(COMPOSE) exec -it php-cli sh
shell-fpm:
	$(COMPOSE) exec -it php-cli sh
shell-nginx:
	$(COMPOSE) exec -it nginx sh
debug-compose:
	$(COMPOSE) config
healthcheck-elastic:
	curl -X GET "localhost:9200"
healthcheck-logstash:
	echo '{"message":"hello world from Farzan"}' | nc localhost 5000

