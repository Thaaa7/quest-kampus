# Kampus Quest di Docker (dipakai Render paket gratis, lihat render.yaml).
FROM php:8.4-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql pdo_mysql zip gd opcache \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && a2enmod rewrite headers
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-kampus-quest.ini"
COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache-mpm.conf /etc/apache2/mods-available/mpm_prefork.conf
COPY docker/apache-security.conf /etc/apache2/conf-available/kampus-quest.conf
RUN sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
 && a2enconf kampus-quest

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
RUN mkdir -p storage/app/private storage/app/public storage/fonts storage/logs \
        storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache \
 && composer dump-autoload --optimize --no-dev --no-interaction \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan event:cache \
 && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/kq-start
RUN chmod +x /usr/local/bin/kq-start

ENV PORT=10000
EXPOSE 10000
CMD ["kq-start"]
