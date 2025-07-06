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
    sqlite3 \
    libsqlite3-dev

# Cài extension PHP
RUN docker-php-ext-install pdo pdo_sqlite zip

# Cài composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thư mục làm việc
WORKDIR /app

# Copy source code vào container
COPY . .

# Cài package Laravel
RUN composer install --no-dev --optimize-autoloader

# Cache config (nếu .env sẵn sàng)
RUN php artisan config:cache || true

# Expose port & chạy Laravel
EXPOSE 10000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]