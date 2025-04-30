#include docker/config
#export

#COMPOSE = docker compose -p rightmart-test --env-file docker/config -f docker/docker-compose.yaml
COMPOSE = docker compose -p rightmart-test -f docker/docker-compose.yaml
APP_NETWORK = rightmart_default

# Build/Destroy:
setup:
	docker network create $(APP_NETWORK)
teardown:
	docker network rm $(APP_NETWORK);
	#$(DOCKER) stop
build:
	$(COMPOSE) build

# Regular usage:
up:
	$(COMPOSE) up -d
down:
	$(COMPOSE) down

# Helpers:
shell-builder:
	$(COMPOSE) exec -it php-builder bash
debug-compose:
	$(COMPOSE) config