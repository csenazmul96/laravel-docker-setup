FROM php:8.3-fpm

# Arguments
ARG user=laravel
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev  \ 
    libmagickwand-dev \
    imagemagick

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (GD with WebP support)
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip intl opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Copy Xdebug config
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Copy PHP configs
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Install imagick
RUN pecl install imagick && docker-php-ext-enable imagick

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Create Laravel storage directories
RUN mkdir -p \
    /var/www/storage/app/public \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/logs \
    /var/www/bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy entrypoint script BEFORE switching user
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Switch user
USER $user

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
