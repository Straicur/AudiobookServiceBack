#!/bin/bash

if [ ! -f .env.local ]; then
  echo "File .env.local not exist"
  exit 1
fi

symfony console doctrine:database:drop --force
symfony console doctrine:database:create
echo | symfony console doctrine:migrations:migrate

roles=("Administrator" "User" "Guest")

for i in "${roles[@]}"; do
  symfony console audiobookservice:roles:add "$i"
done

symfony console audiobookservice:institution:add "980921223" 4 20
symfony console audiobookservice:admin:add "Damian" "Mosiński" "admin@audio.com" "980921223" "zaq12wsx"
symfony console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian12@gmail.com" "980921223" "zaq12wsx" "User"
symfony console audiobookservice:category:add "Bajki"
symfony console audiobookservice:category:add "Kreskówki" "Bajki"
symfony console audiobookservice:category:add "Anime" "Bajki"
