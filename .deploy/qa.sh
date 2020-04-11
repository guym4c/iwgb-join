#!/bin/bash
cd /var/repo/iwgb-members-qa || exit 1

rsync -a . /var/www/iwgb-members-qa --delete --exclude .git --exclude .deploy --exclude .github --exclude vendor --exclude .gitignore

cd /var/www/iwgb-members-qa/public || exit 1
mv index.php index.temp.php
mv maintenance.php index.php

cd /var/repo/iwgb-members-qa-static || exit 1
rsync -a . /var/www/iwgb-members-qa

chown -R www-data:www-data /var/www/iwgb-members-qa
chmod -R 774 /var/www/iwgb-members-qa
runuser -l deploy -c 'cd /var/www/iwgb-members-qa && composer install'

cd /var/www/iwgb-members-qa/public || exit 1
mv index.php maintenance.php
mv index.temp.php index.php