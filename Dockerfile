FROM php:8.3-cli

# Dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        bcmath \
        exif \
        pcntl \
        zip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier uniquement les fichiers Composer
COPY composer.json composer.lock* ./

# Installer les dépendances PHP sans exécuter les scripts Laravel
RUN composer install \
    --no-dev \
    --no-scripts \
    --optimize-autoloader

# Copier le reste du projet
COPY . .

# Créer les dossiers Laravel
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    bootstrap/cache

# Permissions
RUN chmod -R 777 storage bootstrap/cache

# Exécuter les scripts Composer
RUN composer dump-autoload --optimize
RUN php artisan package:discover --ansi

# Dépendances Node
RUN npm install

# Compiler Vite
RUN npm run build

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}