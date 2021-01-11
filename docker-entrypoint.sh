#!/usr/bin/env bash

echo 'Start docker-entrypoint.sh'
echo "Environment: $APP_ENV"

set -u

composer run-script post-install-cmd

if [ "$APP_ENV" = "dev" ]
then
    composer install --no-interaction --no-suggest --prefer-dist
    # no cache:warmup needed for dev
elif [ "$APP_ENV" = "production" ]
then
    # composer install already executed in Dockerfile
    php bin/console cache:warmup --env=production
elif [ "$APP_ENV" = "acceptance" ]
then
    # composer install already executed in Dockerfile
    php bin/console cache:warmup --env=acceptance
fi

cp docker/nginx/nginx.conf /etc/nginx/nginx.conf
cp docker/nginx/vhost.conf /etc/nginx/conf.d/vhost.conf
cp docker/php/extra-$APP_ENV.ini /etc/php/7.3/fpm/conf.d/99-extra.ini
cp docker/php/fpm-pool.conf /etc/php/7.3/fpm/pool.d/www.conf

touch var/log/dev.log
touch var/log/acceptance.log
touch var/log/production.log
mkdir var/tmp/
chown -R www-data:www-data var

tail -f var/log/dev.log &
tail -f var/log/acceptance.log &
tail -f var/log/production.log &

nginx
php-fpm7.3 -F
