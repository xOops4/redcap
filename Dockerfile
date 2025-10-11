FROM php:8.2-apache

# Installer extensions PHP nécessaires et mariadb-client
RUN apt-get update && apt-get install -y \
    libpng-dev libzip-dev libonig-dev mariadb-client unzip curl \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip mbstring \
    && a2enmod rewrite dir

# Forcer index.php en premier dans DirectoryIndex
RUN sed -i 's|DirectoryIndex .*|DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm|' /etc/apache2/mods-enabled/dir.conf

# Autoriser .htaccess
RUN echo '<Directory /var/www/html>\n    AllowOverride All\n</Directory>' > /etc/apache2/conf-available/allowoverride.conf \
    && a2enconf allowoverride

# Permissions REDCap
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/temp /var/www/html/edocs \
    && chown -R www-data:www-data /var/www/html/temp /var/www/html/edocs

# Config PHP pour logs d’erreurs
RUN mkdir -p /var/log/php && touch /var/log/php/php_errors.log && chmod 666 /var/log/php/php_errors.log
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini
RUN echo "error_log = /var/log/php/php_errors.log" >> /usr/local/etc/php/php.ini

EXPOSE 80
CMD ["apache2-foreground"]
