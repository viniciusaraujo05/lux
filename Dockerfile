# Use a imagem oficial do PHP 8.2 FPM Alpine (leve e estável)
FROM php:8.2-fpm-alpine

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Instale dependências do sistema e extensões PHP necessárias para Laravel
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    postgresql-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Instale o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copie os arquivos do projeto
COPY . .

# Instale as dependências PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Instale as dependências Node e faça o build dos assets
RUN npm install && npm run build || true

# Permissões corretas para storage e cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuração do PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Exponha a porta 80
EXPOSE 80

# Comando para iniciar o PHP-FPM
CMD ["php-fpm"]