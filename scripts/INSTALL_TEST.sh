#!/bin/bash

pwd

if [ ! -f .env.test.local ]; then
  printf "File .env.test.local not exist\n"
  exit 1
fi

APP_ENV=test symfony console doctrine:database:drop --force
APP_ENV=test symfony console doctrine:database:create
echo | APP_ENV=test symfony console doctrine:migrations:migrate

roles=("Administrator" "User" "Guest")

for i in "${roles[@]}"; do
  APP_ENV=test symfony console audiobookservice:roles:add "$i"
done

APP_ENV=test symfony console audiobookservice:institution:add "980921223" 4 3
APP_ENV=test symfony console audiobookservice:admin:add "Damian" "Mosiński" "admin@audio.com" "980921223" "zaq12wsx"
APP_ENV=test symfony console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian11@gmail.com" "980921223" "zaq12wsx" "User"
APP_ENV=test symfony console audiobookservice:users:create "Krystian" "Jakiś" "mosinskidamian12@gmail.com" "980921224" "zaq12wsx" "User"
APP_ENV=test symfony console audiobookservice:users:create "Marcin" "Gogo" "mosinskidamian13@gmail.com" "980921225" "zaq12wsx" "User"
APP_ENV=test symfony console audiobookservice:users:create "Michał" "Bobski" "mosinskidamian14@gmail.com" "980921226" "zaq12wsx" "User"
APP_ENV=test symfony console audiobookservice:users:create "Kamil" "Kwiatkowski" "mosinskidamian15@gmail.com" "980921227" "zaq12wsx" "Guest"

APP_ENV=test symfony console audiobookservice:category:add "Bajki"
APP_ENV=test symfony console audiobookservice:category:add "Kreskówki" "Bajki"
APP_ENV=test symfony console audiobookservice:category:add "Anime" "Bajki"
APP_ENV=test symfony console audiobookservice:category:add "Kryminały"
APP_ENV=test symfony console audiobookservice:category:add "Klasyczne" "Kryminały"
APP_ENV=test symfony console audiobookservice:category:add "Skandynawskie" "Kryminały"
APP_ENV=test symfony console audiobookservice:category:add "Szpiegowskie" "Kryminały"
APP_ENV=test symfony console audiobookservice:category:add "Polskie" "Klasyczne"
APP_ENV=test symfony console audiobookservice:category:add "Nowe" "Skandynawskie"
APP_ENV=test symfony console audiobookservice:category:add "Rozwojowe"
APP_ENV=test symfony console audiobookservice:category:add "Naukowe"
