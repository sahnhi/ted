#syntax=docker/dockerfile:1

ARG COMPOSER_VERSION=2.4
ARG PHP_VERSION=8.1

FROM webdevops/php-nginx:${PHP_VERSION}-alpine AS php-base

LABEL MAINTAINER="dev@tediber.com"

ARG APP_ENV=prod

ENV WEB_DOCUMENT_ROOT=/app/public
# Used for supervisor so it does not crash
# See : https://console.doit.com/customers/gHduyg3HfAgHGwH4nBnN/support/tickets/121586
ENV LOG_STDOUT=/dev/stdout
ENV LOG_STDERR=/dev/stdout
# Used for composer
ENV COMPOSER_ALLOW_SUPERUSER=1
# Add binaries into path
ENV PATH="${PATH}:/root/.composer/vendor/bin"

### install wkhtmltopdf ###
COPY --from=surnet/alpine-wkhtmltopdf:3.18.0-0.12.6-small /bin/wkhtmltopdf /usr/bin/wkhtmltopdf

### Copy config files ###
COPY --link docker/php/app.ini $PHP_INI_DIR/conf.d/
COPY --link docker/nginx/vhost.conf /opt/docker/etc/nginx/vhost.common.d/tediber-vhost.conf
COPY --link docker/nginx/php.conf /opt/docker/etc/nginx/vhost.common.d/10-php.conf
COPY --link docker/nginx/nginx.conf /opt/docker/etc/nginx/nginx.conf

### Install basic tools ###
RUN \
    apk update && \
    apk add --no-cache \
	acl \
	file \
	gettext \
	git

### Install php extensions ###
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN install-php-extensions \
    @composer-$COMPOSER_VERSION \
	exif \
	gd \
	intl \
	pdo_mysql \
	zip \
    apcu \
    excimer \
    amqp

### Update nginx config ###
RUN ln -f -s /var/lib/nginx/logs /var/log/nginx && \
	sed -i 's|ln -f -s /var/lib/nginx/logs /var/log/nginx|#ln -f -s /var/lib/nginx/logs /var/log/nginx (Already hardcoded in dockerfile RUN command)|g' /opt/docker/provision/entrypoint.d/20-nginx.sh

FROM php-base AS php-main

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/php-prod.ini $PHP_INI_DIR/conf.d/php.ini

### Install app
WORKDIR /app

COPY --link app/composer.json app/composer.lock app/symfony.lock ./
COPY --link app/.env app/.env.demo app/.env.staging app/.env.prod app/.env.test ./
COPY --link app/bin bin/
COPY --link app/config config/
COPY --link app/public public/
COPY --link app/src src/
COPY --link app/templates templates/
COPY --link app/translations translations/
COPY --link app/plugins plugins/

RUN \
	composer install --no-cache --prefer-dist --no-autoloader --no-scripts --no-progress --ignore-platform-reqs --no-dev \
	&& composer dump-autoload --no-dev --classmap-authoritative \
	&& mkdir -p public/media/image \
	&& chmod +x bin/console \
	&& chmod +x bin/migrations-fix.sh \
	&& chmod +x bin/migrations-sylius-18.sh \
	&& sync;

VOLUME /app/public/media

# ENTRYPOINT instructions will break the webdevops base image!
# see for details https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/base.html
# TLDR; COPY your scripts inside `/opt/docker/provision/entrypoint.d/` dir if you want it to run at container run
# TLDR; COPY your confs inside `/opt/docker/etc/supervisor.d/` dir if you want it to run in the background
COPY --link docker/php/docker-entrypoint.sh /opt/docker/provision/entrypoint.d/tediber-entrypoint-10.sh

EXPOSE 8080

FROM php-main AS php-workers

# Message consume have to be run by supervisord in order for webdevops entrypoint to be fully loaded
COPY gcp/config/messenger-worker.conf /opt/docker/etc/supervisor.d/tediber-amqp-worker.conf

FROM php-main AS php-dev

ENV APP_ENV=dev XDEBUG_MODE=off

RUN install-php-extensions \
	xdebug-^3.2@stable

# Copy dev php conf
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
# Override Php conf
COPY --link docker/php/php-dev.ini $PHP_INI_DIR/conf.d/php.ini
# Add entrypoint before main entrypoint
COPY --link docker/php/docker-entrypoint-dev.sh /opt/docker/provision/entrypoint.d/tediber-entrypoint-00.sh
