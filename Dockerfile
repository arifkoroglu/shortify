FROM php:8.2-fpm

# Sistem bağımlılıklarını kuruyoruz
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# PHP önbelleğini temizliyoruz
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Laravel için gerekli PHP eklentilerini kuruyoruz
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Redis eklentisini PECL ile kurup aktif ediyoruz
RUN pecl install redis && docker-php-ext-enable redis

# Composer'ı resmi imajdan kopyalıyoruz
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Mevcut klasördeki kodları içeri aktarıyoruz
COPY . /var/www

# GÜNCELLENEN KISIM: Klasörler yoksa önce oluştur, sonra izinlerini ver!
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]