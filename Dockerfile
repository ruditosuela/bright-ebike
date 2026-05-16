FROM php:8.2-apache

# Enable mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files
COPY . /var/www/html/

# Create uploads folder
RUN mkdir -p /var/www/html/uploads/profiles

# Set permissions
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 755 /var/www/html/uploads

EXPOSE 80