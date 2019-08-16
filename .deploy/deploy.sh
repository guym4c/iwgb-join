#!/bin/bash

exec > /root/webhooks/iwgb-members/output.log 2>&1

git fetch --all
git checkout --force "origin/master"

cd /var/www/members.iwgb.org.uk/var/log || exit
rsync -a . /var/repo/members.iwgb.org.uk-static/var/log

cd /var/repo/members.iwgb.org.uk || exit
rsync -a . /var/www/members.iwgb.org.uk --delete --exclude .git --exclude .deploy --exclude vendor

cd /var/repo/members.iwgb.org.uk-static || exit
rsync -a . /var/www/members.iwgb.org.uk

cd /var/www/members.iwgb.org.uk || exit
export COMPOSER_HOME=/usr/local/bin
composer install
composer update
mkdir var
mkdir var/doctrine
mkdir var/log
chmod -R 777 var
