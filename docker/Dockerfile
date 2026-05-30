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

# Install docker-php-extension-installer (The community standard for robust PHP extensions)
# We use a specific version for stability and avoid ADD from remote during metadata load
RUN curl -sSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions zip bcmath pdo_mysql redis grpc protobuf

# Install Composer directly via curl to bypass broken docker registry mirrors
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Install Xray binary for validation
RUN ARCH=$(uname -m) && \
    if [ "$ARCH" = "x86_64" ]; then XRAY_ARCH="64"; elif [ "$ARCH" = "aarch64" ]; then XRAY_ARCH="arm64-v8a"; else XRAY_ARCH="64"; fi && \
    curl -L https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-${XRAY_ARCH}.zip -o xray.zip && \
    unzip xray.zip xray -d /usr/local/bin/ && \
    chmod +x /usr/local/bin/xray && \
    rm xray.zip

# Adjust permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
