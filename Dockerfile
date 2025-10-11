FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev libonig-dev mariadb-client \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip mbstring \
    && a2enmod rewrite dir

# Forcer index.php en premier
RUN sed -i 's|DirectoryIndex .*|DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm|' /etc/apache2/mods-enabled/dir.conf

# Autoriser .htaccess
RUN echo '<Directory /var/www/html>\n    AllowOverride All\n</Directory>' > /etc/apache2/conf-available/allowoverride.conf \
    && a2enconf allowoverride

# Permissions correctes
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
