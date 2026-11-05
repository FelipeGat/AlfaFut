# =========================================
# Stage 1: dependencias PHP e autoload
# =========================================
FROM composer:2 AS php-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --no-dev --no-scripts --optimize --classmap-authoritative --no-interaction

# =========================================
# Stage 2: build de assets (Tailwind/Vite)
# =========================================
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
# O Tailwind tambem le as views de paginacao do framework (tailwind.config.js)
COPY --from=php-deps /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# =========================================
# Stage 3: imagem final (nginx + PHP-FPM sob supervisor)
# =========================================
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo_mysql opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/alfafut.ini "$PHP_INI_DIR/conf.d/zz-alfafut.ini"
COPY docker/php/fpm.conf /usr/local/etc/php-fpm.d/zzz-alfafut.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/alfafut-entrypoint

WORKDIR /var/www/html
COPY --from=php-deps /app ./
COPY --from=assets /app/public/build ./public/build

# O codigo fica so para leitura; www-data escreve apenas em storage e bootstrap/cache
RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache

# Variaveis padrao de producao; segredos, banco e e-mail vem do ambiente.
# Sem MAIL_MAILER real o script de entrada recusa subir (alfafut:checar-producao).
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    DB_CONNECTION=mysql \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database

USER www-data
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["alfafut-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
