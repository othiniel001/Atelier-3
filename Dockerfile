# Image de déploiement de l'application « ecole-frais » (Railway, Render, tout hébergeur Docker).
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libzip-dev libsqlite3-dev unzip \
    && docker-php-ext-install gd zip bcmath pdo_mysql \
    && { a2dismod -f mpm_event mpm_worker || true; } && a2enmod mpm_prefork rewrite \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache sert le dossier public/ et écoute sur le port fourni par l'hébergeur ($PORT).
ENV PORT=8080 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY ecole-frais/docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN sed -ri 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf

WORKDIR /var/www/html

# Dépendances PHP d'abord (cache Docker), puis le code.
COPY ecole-frais/composer.json ecole-frais/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY ecole-frais/ ./
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/recus bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod +x docker/entrypoint.sh

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_LOCALE=fr \
    APP_FAKER_LOCALE=fr_FR \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    SMS_DRIVER=log

EXPOSE 8080
ENTRYPOINT ["docker/entrypoint.sh"]
