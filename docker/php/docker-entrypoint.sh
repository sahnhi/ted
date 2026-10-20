#!/bin/sh
set -e

rm -rf /app/var
bin/console cache:warmup -e "$APP_ENV"
chown -R application:application /app/var
