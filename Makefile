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
	symfony console  cache:pool:clear stock_cache
unitTests:
	symfony run bin/phpunit
tests: clearStock unitTests
	symfony console  cache:pool:clear stock_cache
	@echo 'Test Completed'
migration:
	symfony console make:migration
migrate:
	symfony console doctrine:migrations:migrate
	APP_ENV=test symfony console doctrine:migrations:migrate
serverStart:
	symfony server:start -d
serverStop:
	symfony server:stop
entity:
	symfony console make:entity
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