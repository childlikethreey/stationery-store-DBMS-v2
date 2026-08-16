FROM php:8.2-apache

WORKDIR /var/www/html/

COPY . .

RUN docker-php-ext-install pdo_mysql

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf