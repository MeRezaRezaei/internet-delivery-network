FROM php:8.5-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    openssh-client \
    libz-dev \
    libssl-dev \
    build-essential \
    autoconf \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install zip bcmath pdo_mysql

# Install grpc and protobuf via pecl
RUN pecl channel-update pecl.php.net \
    && MAKEFLAGS="-j$(nproc)" pecl install grpc \
    && MAKEFLAGS="-j$(nproc)" pecl install protobuf \
    && docker-php-ext-enable grpc protobuf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Adjust permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
