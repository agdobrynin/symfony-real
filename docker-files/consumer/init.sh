#!/bin/sh
cd $APP_DIR && \
composer install && \
php bin/console doctrine:migrations:migrate -n && \
php bin/console messenger:consume async
