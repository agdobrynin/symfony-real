FROM php:7.3-fpm

ARG app_dir

# TODO for docker make env DEV or PROD
COPY php.ini-development /usr/local/etc/php/conf.d/docker-php-config.ini

# TODO for docker make env DEV and TEST need "pcov" but for PROD exclude "pcov"
RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpq-dev \
    && docker-php-ext-install intl opcache pdo pdo_pgsql pgsql \
    && pecl install apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR $app_dir

EXPOSE 9000