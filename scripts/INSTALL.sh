#!/bin/bash

if [ ! -f .env.local ]; then
  echo "File .env.local not exist"
  exit 1
fi

composer install
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
echo | php bin/console doctrine:migrations:migrate

roles=("Administrator" "User" "Guest" "Recruiter")

for i in "${roles[@]}"; do
  php bin/console audiobookservice:roles:add "$i"
done

php bin/console audiobookservice:clear:audiobooks

php bin/console audiobookservice:institution:add "980921222" 4 20
php bin/console audiobookservice:admin:add "Damian" "Mosiński" "admin@audiobookback.icu" "980921222" "zaq12wsx"
php bin/console audiobookservice:users:create "Damian" "Mosiński" "mosinskidamian11@gmail.com" "980921223" "zaq12wsx" "User"
php bin/console audiobookservice:users:create "Krystian" "Jakiś" "mosinskidamian12@gmail.com" "+48 669972317" "zaq12wsx" "User"
php bin/console audiobookservice:users:create "Marcin" "Gogo" "mosinskidamian13@gmail.com" "980921225" "zaq12wsx" "User"
php bin/console audiobookservice:users:create "Michał" "Bobski" "mosinskidamian14@gmail.com" "980921226" "zaq12wsx" "User"
php bin/console audiobookservice:users:create "Kamil" "Kwiatkowski" "mosinskidamian15@gmail.com" "980921227" "zaq12wsx" "Guest"
php bin/console audiobookservice:users:create "Kamil" "Rekruter" "recq@audiobookback.icu" "980921228" "zaq12wsx" "User" "Guest" "Recruiter"

php bin/console audiobookservice:category:add "Anglojęzyczne"
php bin/console audiobookservice:category:add "Polskie"

php bin/console audiobookservice:category:add "Fikcja dziecięca"
php bin/console audiobookservice:category:add "Akcja i przygoda" "Fikcja dziecięca"
php bin/console audiobookservice:category:add "Zwierzęta i natura" "Fikcja dziecięca"
php bin/console audiobookservice:category:add "Non-Fiction"
php bin/console audiobookservice:category:add "Historia" "Non-Fiction"
php bin/console audiobookservice:category:add "Psychologia" "Non-Fiction"
php bin/console audiobookservice:category:add "Sci-Fi"
php bin/console audiobookservice:category:add "Westen"

php bin/console audiobookservice:category:add "Opowiadanie"
php bin/console audiobookservice:category:add "Romantyzm"
php bin/console audiobookservice:category:add "Pozytywizm"
php bin/console audiobookservice:category:add "Powieść"
php bin/console audiobookservice:category:add "Wiersz"

php bin/console  cache:pool:clear stock_cache