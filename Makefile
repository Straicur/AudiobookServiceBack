SHELL := /bin/bash

start:
	@echo "make [option]"
	@echo "OPTIONS:"
	@echo '	install         - installing new instance of api'
	@echo '	installTest     - installing only test instance of api'
	@echo '	installNoTest   - installing test and dev instance of api without running tests'
	@echo '	tests           - make all tests'
	@echo '	migration       - create doctrine migration'
	@echo '	migrate         - migrate database'
	@echo '	serverStart     - migrate database'
	@echo '	serverStop      - migrate database'
	@echo '	entity          - create entity'
	@echo '	clearStock      - clear given stock'
	@echo '	lint      		- phpcs'
	@echo '	lint-fix        - phpcbf'
clearStock:
	php bin/console  cache:pool:clear stock_cache
unitTests:
	symfony run bin/phpunit
tests: clearStock unitTests
	php bin/console  cache:pool:clear stock_cache
	@echo 'Test Completed'
migration:
	php bin/console make:migration
migrate:
	php bin/console doctrine:migrations:migrate
	APP_ENV=test php bin/console doctrine:migrations:migrate
serverStart:
	symfony server:start -d
serverStop:
	symfony server:stop
entity:
	php bin/console make:entity
installTest:
	./scripts/INSTALL_TEST.sh
install: installTest tests
	./scripts/INSTALL.sh
installNoTest: installTest
	./scripts/INSTALL.sh
lint:
	./vendor/bin/phpcs --standard=ruleset.xml ./src
lint-fix:
	./vendor/bin/phpcbf --standard=ruleset.xml ./src