#!/bin/bash

exec > /root/webhooks/iwgb-members/output.log 2>&1

git fetch --all
git checkout --force "origin/master"

rsync -a . /var/www/members.iwgb.org.uk --delete --exclude .git --exclude .deploy --exclude vendor

# shellcheck disable=SC2164
cd /var/repo/members.iwgb.org.uk-static
rsync -a . /var/www/members.iwgb.org.uk

# shellcheck disable=SC2164
cd /var/www/members.iwgb.org.uk
export COMPOSER_HOME=/usr/local/bin
composer install
composer update
mkdir var
mkdir var/doctrine
mkdir var/log
chmod -R 777 var
