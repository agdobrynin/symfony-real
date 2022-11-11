SHELL := /bin/bash
SHUTDOWN_TEST_ENV := $(shell php bin/console doctrine:database:drop --force --env=test || true)

tests-unit:
	@echo "📦 Run Unit testsuite"
	php bin/phpunit --testsuite=unit

tests-integration:
	@echo "🙈 Run Integration testsuite"
	php bin/phpunit --testsuite=integration

tests-functional: preload-test-env
	@echo "🌐 Run Functional testsuite"
	php bin/phpunit
	$(SHOTDOWN_TEST_ENV)

tests-all: preload-test-env
	@echo "🌈 Run all testsuite"
	php bin/phpunit tests/
	$(SHOTDOWN_TEST_ENV)

preload-test-env:
	@echo "⚙ Run preparing environment for testing"
	$(SHOTDOWN_TEST_ENV)
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:migrations:migrate -n --env=test
	php bin/console doctrine:fixtures:load -n --env=test
.PHONY: preload-test-env
