FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y \
        apt-utils \
    && apt-get install -y \
        libpq-dev \
        libzip-dev \
    && pecl install xdebug redis \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-install -j$(nproc) sockets \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ADD entrypoint.sh /etc/entrypoint.sh
RUN ["chmod", "+x", "/etc/entrypoint.sh"]

ENTRYPOINT [ "/etc/entrypoint.sh" ]
