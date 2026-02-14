FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y \
        git \
        libzip-dev \
        unzip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN git clone https://github.com/phpactor/phpactor.git /opt/phpactor \
    && cd /opt/phpactor \
    && composer install --no-dev --optimize-autoloader \
    && ln -s /opt/phpactor/bin/phpactor /usr/local/bin/phpactor
