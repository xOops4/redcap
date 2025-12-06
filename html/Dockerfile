FROM php:8.3-fpm

# Install dependencies and extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    gd \
    zip \
    mbstring \
    exif \
    pcntl \
    bcmath \
    xml

# Installer ImageMagick
# RUN apt-get update && apt-get install -y imagemagick

# Installer l'extension PHP Imagick
# RUN pecl install imagick && docker-php-ext-enable imagick

# Copy your HTML folder content (si vous copiez depuis le build)
# COPY html/ /var/www/html/

# Créer le répertoire et définir les permissions AVANT de copier
RUN mkdir -p /var/www/html \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Définir le répertoire de travail
WORKDIR /var/www/html

# Enable opcache for better performance
RUN docker-php-ext-enable opcache

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*