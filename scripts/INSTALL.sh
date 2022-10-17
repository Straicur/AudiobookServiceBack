#!/bin/bash

if [ ! -f .env.local ]; then
  echo "File .env.local not exist"
  exit 1
fi

symfony console doctrine:database:drop --force
symfony console doctrine:database:create
echo | symfony console doctrine:migrations:migrate

roles=("Administrator" "User" "Guest")

for i in "${roles[@]}"
do
  symfony console audiobookservice:roles:add "$i"
done
  symfony console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian11@gmial.com" "980921223" "zaq12wsx" "Administrator" "User"
