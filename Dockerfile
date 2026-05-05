FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 10000

# Create sqlite file + permissions
RUN mkdir -p /tmp && touch /tmp/database.sqlite && chmod 777 /tmp/database.sqlite

CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"