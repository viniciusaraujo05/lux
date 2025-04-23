# Dockerfile for Laravel 8.4+ (PHP 8.1+)
# Suitable for use with Laravel Sail and your docker-compose.yml

FROM ubuntu:22.04

# Install system dependencies
RUN apt-get update \
    && apt-get install -y \
        curl \
        gnupg2 \
        ca-certificates \
        zip \
        unzip \
        git \
        supervisor \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libcurl4-openssl-dev \
        libssl-dev \
        libpq-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libmcrypt-dev \
        nano \
        nodejs \
        npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP and extensions
RUN apt-get update \
    && apt-get install -y software-properties-common \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update \
    && apt-get install -y php8.1 php8.1-cli php8.1-fpm php8.1-json php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath php8.1-pgsql php8.1-readline php8.1-sqlite3 php8.1-intl php8.1-xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose ports
EXPOSE 80

# Start PHP-FPM server
CMD ["php-fpm8.1", "-F"]
