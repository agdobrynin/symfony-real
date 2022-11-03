FROM php:7.3-fpm

ARG app_dir
ENV APP_DIR $app_dir

# TODO for docker make env DEV or PROD
COPY php.ini-development /usr/local/etc/php/conf.d/docker-php-config.ini

RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpq-dev \
    && docker-php-ext-install intl opcache pdo pdo_pgsql pgsql \
    && pecl install apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR $app_dir

COPY init.sh /init.sh
RUN chmod +x /init.sh
CMD ["/init.sh"]

EXPOSE 9000