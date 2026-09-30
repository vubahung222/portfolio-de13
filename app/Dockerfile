FROM php:8.3-fpm-alpine

# Install PDO MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

# Create a dedicated non-root user/group and give ownership of the webroot.
# php-fpm-alpine already ships a "www-data" user (uid 82); we reuse it and
# make sure the process runs as non-root.
WORKDIR /var/www/html

# Ensure runtime dirs are writable by www-data
RUN mkdir -p /var/www/html/public /var/www/html/src \
    && chown -R www-data:www-data /var/www/html

# Run php-fpm as the unprivileged www-data user
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
