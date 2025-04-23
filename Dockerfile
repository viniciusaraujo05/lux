# Dockerfile for Laravel 8.4+ (PHP 8.1+)
# Suitable for use with Laravel Sail and your docker-compose.yml

# Dockerfile otimizado para Laravel 8.4+ usando Laravel Sail
FROM laravelsail/php81-composer:latest

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie o código do projeto
COPY . .

# Instale as dependências PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Instale as dependências Node e faça o build dos assets
RUN npm install && npm run build || true

# Permissões corretas para storage e cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["php-fpm"]
