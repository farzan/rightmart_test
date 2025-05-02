FROM php:8.3-fpm-alpine

# Install necessary packages
RUN apk add --no-cache bash openssh-client

# Install PHP pcntl
RUN docker-php-ext-install pcntl

WORKDIR /var/www
