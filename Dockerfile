FROM php:8.1-apache
# (Ou ta version spécifique)

# 1. Mettre à jour et installer les dépendances SYSTÈME requises pour GD
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Configurer GD pour qu'il prenne en compte le JPEG et FreeType (important pour Camagru)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# 3. Installer les extensions PHP
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# 1. Active le module de réécriture d'URL (indispensable pour le MVC/Routeur)
RUN a2enmod rewrite

# 2. Change la racine du serveur vers le dossier 'public'
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# 3. Applique ce changement dans les fichiers de config d'Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf