FROM composer:1.5
FROM debian:buster

EXPOSE 80

RUN apt-get -y update \
 && apt-get -y upgrade \
 && apt-get -y install nano curl bash \
 && apt-get -y install nginx \
 && apt-get -y install git \
 && apt-get -y install php7.2 php7.2-fpm php7.2-curl php7.2-intl php7.2-json php7.2-mbstring php7.2-opcache php7.2-zip php7.2-xml \
 && apt-get -y install libreoffice \
 && apt-get -y install imagemagick 

COPY --from=composer:1.5 /usr/bin/composer /usr/bin/composer

WORKDIR /
RUN mkdir /srv/app
COPY . /srv/app
WORKDIR /srv/app

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer install --prefer-dist --no-progress --no-suggest --no-scripts
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

RUN mkdir /var/run/php-fpm \
 && mkdir /run/php \
 && mkdir /var/www/.cache \
 && chown www-data:www-data /var/www/.cache \
 && chmod u+rwx /var/www/.cache \
 && mkdir /var/www/.config \
 && chown www-data:www-data /var/www/.config \
 && chmod u+rwx /var/www/.config

ENTRYPOINT /srv/app/docker-entrypoint.sh

