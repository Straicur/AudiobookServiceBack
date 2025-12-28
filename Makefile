SHELL := /bin/bash

start:
	@echo "make [option]"
	@echo "OPTIONS:"
	@echo '	install         - installing new instance of api'
	@echo '	installTest     - installing only test instance of api'
	@echo '	systemTests     - make all tests'
	@echo '	migration       - create doctrine migration'
	@echo '	migrate         - migrate database'
	@echo '	serverStart     - migrate database'
	@echo '	serverStop      - migrate database'
	@echo '	entity          - create entity'
	@echo '	clearStock      - clear given stock'
clearStock:
	php bin/console cache:pool:clear stock_cache
systemTests:
	./scripts/TESTS.sh
migration:
	php bin/console make:migration
migrate:
	php bin/console doctrine:migrations:migrate
	APP_ENV=test php bin/console doctrine:migrations:migrate
serverStart:
	php bin/console server:start -d
serverStop:
	php bin/console server:stop
entity:
	php bin/console make:entity
installTest:
	./scripts/INSTALL_TEST.sh
install:
	./scripts/INSTALL.sh