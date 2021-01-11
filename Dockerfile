FROM composer:1.10
FROM debian:buster

EXPOSE 80

RUN apt-get -y update \
 && apt-get -y upgrade \
 && apt-get -y install nano curl bash \
 && apt-get -y install nginx \
 && apt-get -y install unzip \
 && apt-get -y install git \
 && apt-get -y install php7.3 php7.3-fpm php7.3-curl php7.3-intl php7.3-json php7.3-mbstring php7.3-opcache php7.3-zip php7.3-xml \
 && apt-get -y install libreoffice \
 && apt-get -y install imagemagick 

COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer

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

