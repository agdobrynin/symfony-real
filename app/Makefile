SHELL := /bin/bash

tests-unit:
	@echo "📦 Run Unit testsuite"
	@php bin/phpunit --testsuite=unit

tests-integration:
	@echo "🙈 Run Integration testsuite"
	@php bin/phpunit --testsuite=integration

tests-functional: preload-test-env
	@echo "🌐 Run Functional testsuite"
	@php bin/phpunit
	@php bin/console doctrine:database:drop --if-exists --force --env=test || true

tests-all: preload-test-env
	@echo "🌈 Run all testsuite"
	@php bin/phpunit tests/
	@php bin/console doctrine:database:drop --if-exists --force --env=test || true

preload-test-env:
	@echo "⚙ Run preparing environment for testing"
	@php bin/console doctrine:database:drop --if-exists --force --env=test || true
	@php bin/console doctrine:database:create --env=test
	@php bin/console doctrine:migrations:migrate -n --env=test
	@php bin/console doctrine:fixtures:load -n --env=test
.PHONY: preload-test-env
