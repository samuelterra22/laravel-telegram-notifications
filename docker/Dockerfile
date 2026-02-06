FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    curl-dev \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install zip pcntl \
    && pecl install pcov \
    && docker-php-ext-enable pcov

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure PHP for coverage
RUN echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/pcov.ini

WORKDIR /app
