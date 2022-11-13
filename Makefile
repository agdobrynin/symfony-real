tests:
	docker-compose -f docker-compose-test.yml run --rm php sh -c "make tests-$(test)"
build:
	docker-compose build
build-up:
	docker-compose up -d --build
up:
	docker-compose up -d
stop:
	docker-compose stop
container-exec:
	docker-compose exec $(name) bash
front-prod:
	docker-compose run --rm front sh -c "yarn install && yarn encore prod"
front-watch:
	docker-compose run --rm front sh -c "yarn encore dev --watch"
