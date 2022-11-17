SHELL := /bin/sh

tests:
	# Run different testcase. For example make tests type=all or make tests type=unit
	# for details param see file app/Makefile
	@docker-compose -f docker-compose-test.yml run --rm php sh -c "make tests-$(type)"
build:
	@docker-compose build
build-up:
	@docker-compose up -d --build
	@docker-compose ps
up:
	@docker-compose up -d
	@docker-compose ps
stop:
	@docker-compose stop
exec:
	@docker-compose exec $(name) bash
exec-php:
	@docker-compose exec php bash
front-prod:
	@docker-compose run --rm front sh -c "yarn install && yarn encore prod"
front-watch:
	@docker-compose run --rm front sh -c "yarn encore dev --watch"
