#!/bin/bash
# [AT'016] Script d'update pour Clubinfo

sudo ls > /dev/null
cd /srv/upont
touch back/var/cache/maintenance.lock
git pull

export SYMFONY_ENV=prod

cd front
sudo npm install
bower update --allow-root
gulp build-html --type=production
gulp build-templates --type=production
gulp build-js --type=production
gulp build-css --type=production
gulp copy-fonts

cd ../back
sudo composer self-update
composer install --no-dev --optimize-autoloader
bin/console cache:clear --env=prod --no-debug
bin/console do:mi:mi -n
sudo chmod 777 -R var/cache && sudo chmod 777 -R var/logs
rm var/cache/maintenance.lock

cd ..
./utils/newrelic-deploy.sh
