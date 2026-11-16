FROM composer:2.8.12 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader --ignore-platform-req=ext-gd

FROM php:8.3-apache-bookworm

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl libfreetype6-dev libjpeg62-turbo-dev libpng-dev libicu-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring pdo_mysql zip \
    && a2enmod rewrite headers \
    && sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Never ship a locally generated provider cache into the production image.
# Composer's dev-only packages (Pail, Collision, Pest) are absent from this
# image, so rebuild the package manifest after removing the local cache.
RUN rm -f bootstrap/cache/*.php \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

USER www-data

HEALTHCHECK --interval=5s --timeout=3s --start-period=15s --retries=5 \
    CMD curl -fsS http://127.0.0.1/up || exit 1

USER root
CMD ["apache2-foreground"]
