FROM php:8.3-fpm-alpine

# Install PHP pcntl
RUN docker-php-ext-install pcntl

# Install necessary packages
RUN apk add --no-cache \
    bash \
    git \
    openssh-client \
    jq

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN git config --global --add safe.directory /var/www/rightmart_test
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash
RUN apk add symfony-cli

WORKDIR /var/www
