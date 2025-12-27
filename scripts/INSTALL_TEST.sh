#!/bin/bash

pwd

if [ -z "$APP_ENV" ]; then
  echo "Error: APP_ENV is not set!"
  exit 1
fi

if [ "$APP_ENV" != "test" ]; then
  echo "Error: APP_ENV is set to '$APP_ENV', but 'test' is required to run tests."
  exit 1
fi

if [ ! -f .env.test.local ]; then
  printf "File .env.test.local not exist\n"
  exit 1
fi

APP_ENV=test php bin/console doctrine:database:drop --force
APP_ENV=test php bin/console doctrine:database:create
echo | APP_ENV=test php bin/console doctrine:migrations:migrate

roles=("Administrator" "User" "Guest" "Recruiter")

for i in "${roles[@]}"; do
  APP_ENV=test php bin/console audiobookservice:roles:add "$i"
done

APP_ENV=test php bin/console audiobookservice:institution:add "980921222" 4 10
APP_ENV=test php bin/console audiobookservice:admin:add "Damian" "Mosiński" "admin@audiobookback.icu" "980921222" "zaq12wsx"
APP_ENV=test php bin/console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian11@gmail.com" "980921223" "zaq12wsx" "User"
APP_ENV=test php bin/console audiobookservice:users:create "Krystian" "Jakiś" "mosinskidamian12@gmail.com" "980921224" "zaq12wsx" "User"
APP_ENV=test php bin/console audiobookservice:users:create "Marcin" "Gogo" "mosinskidamian13@gmail.com" "980921225" "zaq12wsx" "User"
APP_ENV=test php bin/console audiobookservice:users:create "Michał" "Bobski" "mosinskidamian14@gmail.com" "980921226" "zaq12wsx" "User"
APP_ENV=test php bin/console audiobookservice:users:create "Kamil" "Kwiatkowski" "mosinskidamian15@gmail.com" "980921227" "zaq12wsx" "Guest"
APP_ENV=test php bin/console audiobookservice:users:create "Kamil" "Rekruter" "recq@audiobookback.icu" "980921228" "zaq12wsx" "User" "Guest" "Recruiter"

APP_ENV=test php bin/console audiobookservice:category:add "Bajki"
APP_ENV=test php bin/console audiobookservice:category:add "Kreskówki" "Bajki"
APP_ENV=test php bin/console audiobookservice:category:add "Anime" "Bajki"
APP_ENV=test php bin/console audiobookservice:category:add "Kryminały"
APP_ENV=test php bin/console audiobookservice:category:add "Klasyczne" "Kryminały"
APP_ENV=test php bin/console audiobookservice:category:add "Skandynawskie" "Kryminały"
APP_ENV=test php bin/console audiobookservice:category:add "Szpiegowskie" "Kryminały"
APP_ENV=test php bin/console audiobookservice:category:add "Polskie" "Klasyczne"
APP_ENV=test php bin/console audiobookservice:category:add "Nowe" "Skandynawskie"
APP_ENV=test php bin/console audiobookservice:category:add "Rozwojowe"
APP_ENV=test php bin/console audiobookservice:category:add "Naukowe"
