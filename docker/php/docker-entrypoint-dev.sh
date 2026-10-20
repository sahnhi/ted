#!/bin/sh
set -e

mkdir -p public/media

composer install --prefer-dist --no-progress --no-interaction --ignore-platform-reqs
bin/console assets:install --no-interaction
bin/console sylius:theme:assets:install public --no-interaction

until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
    (>&2 echo "Waiting for MySQL to be ready...")
    sleep 1
done

if [ "$(ls -A src/Doctrine/Migrations/*.php 2> /dev/null)" ]; then
    bin/console doctrine:migrations:migrate --no-interaction
fi

bin/console assets:install public
