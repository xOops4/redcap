FROM php:8.2-apache

# Mettre à jour et installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip mbstring

# Activer les modules Apache
RUN a2enmod rewrite

# Exposer le port
EXPOSE 80

CMD ["apache2-foreground"]