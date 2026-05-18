# =========================================
# Stage 1: instalar dependencias PHP
# =========================================
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# =========================================
# Stage 2: build de assets (Tailwind/Vite)
# =========================================
FROM node:20-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci || npm install
COPY vite.config.js postcss.config.js tailwind.config.js* ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# =========================================
# Stage 3: imagem final (PHP CLI + assets + vendor)
# =========================================
FROM php:8.3-cli-alpine

RUN apk add --no-cache \
        libzip-dev sqlite-dev oniguruma-dev icu-dev libpng-dev \
        bash git \
    && docker-php-ext-install \
        pdo pdo_sqlite zip mbstring intl bcmath \
    && rm -rf /var/cache/apk/*

WORKDIR /app

# Copia o codigo
COPY . .

# Copia vendor (do stage composer) e build (do stage node)
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=node-build /app/public/build ./public/build

# Otimiza autoload + prepara DB SQLite + permissoes
RUN composer dump-autoload --optimize --classmap-authoritative \
    && touch database/database.sqlite \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache database

# Variaveis padrao de producao (sobrescritas pela plataforma)
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    BROADCAST_CONNECTION=log \
    MAIL_MAILER=log

EXPOSE 8080

# Roda migrations + seed na primeira inicializacao e sobe servidor
# Railway/Render passam $PORT — fallback 8080
CMD php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force --seed \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
