FROM chialab/php:7.3-apache

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .

RUN install-php-extensions ssh2 gmp && \
    a2enmod rewrite && \
    apt clean

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

USER www-data

ENV COMPOSER_PROCESS_TIMEOUT=600

RUN composer install -o --no-dev && \
    composer clear-cache && \
    php artisan storage:link

USER root
