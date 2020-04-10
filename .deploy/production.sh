#!/bin/bash
cd /var/repo/iwgb-members || exit 1

rsync -a . /var/www/iwgb-members --delete --exclude .git --exclude .deploy --exclude .github --exclude vendor --exclude .gitignore

cd /var/www/iwgb-members/public || exit 1
mv index.php index.temp.php
mv maintenance.php index.php

cd /var/repo/iwgb-members-static || exit 1
rsync -a . /var/www/iwgb-members

chown -R www-data:www-data /var/www/iwgb-members
chmod -R 774 /var/www/iwgb-members
runuser -l deploy -c 'cd /var/www/iwgb-members && composer install'

cd /var/www/iwgb-members/public || exit 1
mv index.php maintenance.php
mv index.temp.php index.php