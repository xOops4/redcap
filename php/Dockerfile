FROM php:8.3-fpm

# Install dependencies and extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    # Ajout de 'git' pour cloner le dépôt
    git \
    unzip \
    # Librairie ImageMagick et son interface PHP
    imagemagick \
    php-imagick \
    # Extensions PHP critiques pour REDCap
    php-mysql \
    php-curl \
    php-zip \
    php-mbstring \
    php-gd \
    php-xml \
    php-ldap 

# Créer le répertoire et définir les permissions AVANT de copier
RUN mkdir -p /var/www/html \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Définir le répertoire de travail
WORKDIR /var/www/html

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*