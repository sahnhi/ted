FROM composer:2.4.4 as composer

WORKDIR /app

COPY app/composer.json app/composer.lock ./

RUN set -eux; \
	composer --no-cache install --prefer-dist --no-autoloader --no-scripts --no-progress --no-suggest --ignore-platform-reqs;

ARG NODE_VERSION=10

FROM node:10-alpine

WORKDIR /app

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		g++ \
		gcc \
		git \
		make \
		python2 \
	;

COPY app/package.json app/yarn.lock ./

RUN set -eux; \
	yarn install; \
	yarn cache clean

COPY --from=composer /app/vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/UiBundle/Resources/private/
COPY --from=composer /app/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/Resources/private/
COPY --from=composer /app/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/private/

COPY --from=composer /app/vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js
COPY --from=composer /app/vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js

COPY app/src/gulpfile.babel.js src/gulpfile.babel.js
COPY app/src/Resources/private src/Resources/private/

COPY app/gulpfile.babel.js app/.babelrc ./
RUN set -eux; \
	yarn build

COPY docker/nodejs/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["yarn", "watch"]
