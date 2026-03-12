.PHONY: all coverage coverage-js coverage-php test test-js test-php help

CURRENT_VERSION = 1.0.0

all: coverage ## Equivalent to running `make coverage`.

composer: ## Update composer dependencies (only production ones, optimize the autoloader)
	composer update --no-dev --optimize-autoloader

coverage: coverage-js coverage-php ## Run all unit tests and generate code coverage reports.

coverage-js: ## Run JS unit tests and generate code coverage reports.
	cd js && nyc mocha

coverage-php: ## Run PHP unit tests and generate code coverage reports.
	cd tst && XDEBUG_MODE=coverage phpunit 2> /dev/null

test: test-js test-php ## Run all unit tests.

test-js: ## Run JS unit tests.
	cd js && npx mocha

test-php: ## Run PHP unit tests.
	cd tst && phpunit

help: ## Display this help screen.
	@echo "Available targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'
