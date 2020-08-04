.PHONY: help
help:
	@echo 'Usage: make [target]'
	@echo 'Available targets:'
	@echo
	@grep -Eo '^[-_a-z/]+' Makefile | sort

make_migration:
	./bin/console make:migration

migrate:
	./bin/console doctrine:migrations:migrate

reset_db:
	./bin/console doctrine:schema:drop --full-database --force
	./bin/console doctrine:migrations:migrate -n

dev:
	yarn encore dev --watch

dev-hot:
	yarn encore dev-server --hot

fixtures:
	make reset_db
	php bin/console doctrine:fixtures:load --no-interaction

test:
	./bin/phpunit

cron_setup:
	# Note: Needs to be run in project directory
	# https://github.com/rewieer/TaskSchedulerBundle
	# # (crontab -l; echo "$(line)" ) | crontab -
	@./bin/setup_cron.sh
