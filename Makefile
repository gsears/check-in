# Makefile
# Gareth Sears - 2493194S

# This file contains aliases for the main scripts in symfony and otherwise

.PHONY: help
help:
	@echo 'Usage: make [target]'
	@echo 'Available targets:'
	@echo
	@grep -Eo '^[-_a-z/]+' Makefile | sort

## Meta
#### Excecute shell development script
.PHONY: dev
dev:
	@./bin/dev.sh

#### Build dependencies
.PHONY: deps
deps: backend/deps frontend/deps

## Docker DB containers
.PHONY: docker/start
docker/start:
	docker-compose up

.PHONY: docker/stop
docker/stop:
	docker-compose stop

## DB
.PHONY: db/create
db/create:
	./bin/console doctrine:database:create --if-not-exists
	./bin/console doctrine:migrations:migrate -n --allow-no-migration

.PHONY: db/make_migration
db/make_migration:
	./bin/console make:migration

.PHONY: db/migrate
db/migrate:
	./bin/console doctrine:migrations:migrate

.PHONY: db/drop
db/drop:
	./bin/console doctrine:schema:drop --full-database --force

.PHONY: db/reset
db/reset: db/drop db/create

## DB fixtures
.PHONY: fixtures/evaluation
fixtures/evaluation:
	php -d memory_limit=512M bin/console doctrine:fixtures:load --group=evaluation --no-interaction

.PHONY: fixtures/test
fixtures/test:
	php -d memory_limit=512M bin/console doctrine:fixtures:load --group=test --no-interaction

## Backend
.PHONY: backend/deps
backend/deps:
	composer install

.PHONY: backend/start
backend/start:
	backend/status || symfony serve -d

.PHONY: backend/status
backend/status:
	symfony server:status

.PHONY: backend/stop
backend/stop:
	symfony server:stop

.PHONY: backend/cron_setup
backend/cron_setup:
	./bin/setup_cron.sh

## Frontend
.PHONY: frontend/deps
frontend/deps:
	yarn install --frozen-lockfile

.PHONY: frontend/start
frontend/start:
	yarn watch

.PHONY: frontend/build
frontend/build:
	yarn build

## Tests
.PHONY: test/all
test/all:
	./bin/phpunit

.PHONY: test/unit
test/unit:
	./bin/phpunit  --stop-on-failure --testsuite unit

.PHONY: test/functional
test/functional:
	./bin/phpunit  --stop-on-failure --testsuite functional

.PHONY: test/coverage
test/coverage:
	./bin/phpunit --coverage-html docs/testing/coverage
