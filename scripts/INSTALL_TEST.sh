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

for i in "${roles[@]}"
do
  APP_ENV=test symfony console audiobookservice:roles:add "$i"
done
  APP_ENV=test  symfony console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian11@gmial.com" "980921223" "zaq12wsx" "Administrator" "User"