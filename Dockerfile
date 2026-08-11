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
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath exif pcntl zip

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Répertoire de travail
WORKDIR /var/www

# Copie du projet
COPY . .

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installation des dépendances Node
RUN npm install

# Compilation des assets Vite
RUN npm run build

# Permissions
RUN chmod -R 775 storage bootstrap/cache

# Port Render
EXPOSE 10000

# Démarrage de Laravel
CMD php artisan serve --host=0.0.0.0 --port=$PORT