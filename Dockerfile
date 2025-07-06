FROM php:8.2-cli

# Cài gói hệ thống
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

# Cài extension PHP cho MySQL
RUN docker-php-ext-install pdo pdo_mysql zip

# Cài composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thư mục làm việc
WORKDIR /app

# Copy source code
COPY . .

# Cài Laravel packages
RUN composer install --no-dev --optimize-autoloader

# Cache config (nếu có .env)
RUN php artisan config:cache || true

# Cổng Laravel
EXPOSE 10000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]