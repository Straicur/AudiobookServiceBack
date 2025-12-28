#!/bin/bash

if [ -z "$APP_ENV" ]; then
  echo "Error: APP_ENV is not set!"
  exit 1
fi

if [ "$APP_ENV" != "test" ]; then
  echo "Error: APP_ENV is set to '$APP_ENV', but 'test' is required to run tests."
  exit 1
fi

php bin/console  cache:pool:clear stock_cache
php vendor/bin/phpunit

echo 'Test Completed'