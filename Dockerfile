FROM php:8.2-apache

# Enable mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy apache config
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copy project files
COPY . /var/www/html/

# Create uploads folder
RUN mkdir -p /var/www/html/uploads/profiles

# Set permissions
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 777 /var/www/html/uploads

EXPOSE 80