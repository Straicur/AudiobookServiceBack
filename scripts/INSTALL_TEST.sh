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

APP_ENV=symfony console audiobookservice:category:add "Anglojęzyczne"
APP_ENV=symfony console audiobookservice:category:add "Fikcja dziecięca" "Anglojęzyczne"
APP_ENV=symfony console audiobookservice:category:add "Akcja i przygoda" "Fikcja dziecięca"
APP_ENV=symfony console audiobookservice:category:add "Zwierzęta i natura" "Fikcja dziecięca"
APP_ENV=symfony console audiobookservice:category:add "Non-Fiction" "Anglojęzyczne"
APP_ENV=symfony console audiobookservice:category:add "Historia" "Non-Fiction"
APP_ENV=symfony console audiobookservice:category:add "Psychologia" "Non-Fiction"
APP_ENV=symfony console audiobookservice:category:add "Sci-Fi" "Anglojęzyczne"
APP_ENV=symfony console audiobookservice:category:add "Westen" "Anglojęzyczne"
APP_ENV=symfony console audiobookservice:category:add "Polskie"
APP_ENV=symfony console audiobookservice:category:add "Opowiadanie" "Polskie"
APP_ENV=symfony console audiobookservice:category:add "Romantyzm" "Polskie"
APP_ENV=symfony console audiobookservice:category:add "Pozytywizm" "Polskie"
APP_ENV=symfony console audiobookservice:category:add "Powieść" "Polskie"
APP_ENV=symfony console audiobookservice:category:add "Wiersz" "Polskie"
