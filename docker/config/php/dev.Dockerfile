FROM php:8.1-fpm

RUN apt-get -y update && apt-get -y install openssl libssl-dev libcurl4-openssl-dev wget zip zlib1g-dev libzip-dev libxml2-dev git libssh-dev supervisor
RUN rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install curl bcmath zip iconv xml pdo_mysql sockets

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php && composer config --global repos.packagist composer https://packagist.org

COPY supervisor/ /etc/supervisor/conf.d/

RUN printf '[PHP]\nmemory_limit=2G\n' > /usr/local/etc/php/conf.d/memory.ini

WORKDIR /var/www/html

#CMD ["/usr/bin/supervisord"]
