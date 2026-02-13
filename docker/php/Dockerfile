FROM php:8.4-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    mysql-client \
    && docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Create necessary directories before composer install (needed for scripts)
RUN mkdir -p var/cache var/log var/sessions www

# Install PHP dependencies (skip scripts during build to avoid bin/console dependency)
RUN composer install --no-interaction --prefer-dist --no-scripts

# Copy application files
COPY . .

# Regenerate autoloader with optimized classmap
RUN composer dump-autoload --classmap-authoritative --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
