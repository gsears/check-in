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
