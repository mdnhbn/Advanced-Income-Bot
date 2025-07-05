# 1. Base Image: Use the official PHP image with Apache web server
FROM php:8.2-apache

# 2. Enable Apache's mod_rewrite for .htaccess support
RUN a2enmod rewrite

# 3. Set the working directory inside the container
WORKDIR /var/www/html

# 4. Copy all project files into the working directory
COPY . /var/www/html/

# 5. Set ownership of files to the web server user (www-data)
# This is crucial for allowing the bot to write to log files and databases (if using SQLite)
RUN chown -R www-data:www-data /var/www/html

# 6. Expose Port 80, which is the default port for Apache
EXPOSE 80
