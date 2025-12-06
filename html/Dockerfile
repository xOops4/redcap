FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libldap2-dev \
    libbz2-dev \
    libc-client-dev \
    libkrb5-dev \
    libpspell-dev \
    libedit-dev \
    libreadline-dev \
    libsnmp-dev \
    libtidy-dev \
    libxslt1-dev \
    libgmp-dev \
    libmagickwand-dev \
    imagemagick \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath \
        xml \
        intl \
        soap \
        bz2 \
        calendar \
        sockets \
        gettext \
        gmp \
        ldap \
        mysqli \
        opcache \
        pspell \
        readline \
        snmp \
        tidy \
        xsl

# Install Imagick
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create REDCap directory and set permissions
RUN mkdir -p /var/www/html/redcap \
    && chown -R www-data:www-data /var/www/html

# Switch to non-root user
USER www-data

EXPOSE 9000
CMD ["php-fpm"]