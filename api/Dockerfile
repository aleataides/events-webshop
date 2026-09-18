FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo_mysql opcache \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug

# xdebug.mode reads the env var, translated from XDEBUG=true/false at
# container start by docker-entrypoint.sh — toggle via `docker compose up -d`
# after changing .env, no rebuild needed.
RUN { \
        echo "xdebug.mode=\${XDEBUG_MODE}"; \
        echo "xdebug.client_host=host.docker.internal"; \
        echo "xdebug.start_with_request=yes"; \
    } > /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-interaction

COPY . .

EXPOSE 8080
ENTRYPOINT ["./docker-entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
