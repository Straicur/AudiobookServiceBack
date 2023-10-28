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

symfony console audiobookservice:clear:audiobooks

symfony console audiobookservice:institution:add "980921223" 4 20
symfony console audiobookservice:admin:add "Damian" "Mosiński" "admin@audio.com" "980921223" "zaq12wsx"
symfony console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian11@gmail.com" "980921223" "zaq12wsx" "User"
symfony console audiobookservice:users:create "Krystian" "Jakiś" "mosinskidamian12@gmail.com" "980921224" "zaq12wsx" "User"
symfony console audiobookservice:users:create "Marcin" "Gogo" "mosinskidamian13@gmail.com" "980921225" "zaq12wsx" "User"
symfony console audiobookservice:users:create "Michał" "Bobski" "mosinskidamian14@gmail.com" "980921226" "zaq12wsx" "User"
symfony console audiobookservice:users:create "Kamil" "Kwiatkowski" "mosinskidamian15@gmail.com" "980921227" "zaq12wsx" "Guest"

symfony console audiobookservice:category:add "Anglojęzyczne"
symfony console audiobookservice:category:add "Polskie"

symfony console audiobookservice:category:add "Fikcja dziecięca"
symfony console audiobookservice:category:add "Akcja i przygoda" "Fikcja dziecięca"
symfony console audiobookservice:category:add "Zwierzęta i natura" "Fikcja dziecięca"
symfony console audiobookservice:category:add "Non-Fiction"
symfony console audiobookservice:category:add "Historia" "Non-Fiction"
symfony console audiobookservice:category:add "Psychologia" "Non-Fiction"
symfony console audiobookservice:category:add "Sci-Fi" 
symfony console audiobookservice:category:add "Westen" 

symfony console audiobookservice:category:add "Opowiadanie" 
symfony console audiobookservice:category:add "Romantyzm" 
symfony console audiobookservice:category:add "Pozytywizm"
symfony console audiobookservice:category:add "Powieść" 
symfony console audiobookservice:category:add "Wiersz"