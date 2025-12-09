FROM php:8.1-apache

# 1. Installation des dépendances (GD + Mail + Zip)
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    msmtp \
    msmtp-mta \
    && rm -rf /var/lib/apt/lists/*

# 2. Configuration et installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# 3. Configuration de msmtp pour pointer vers MailHog (Port 1025)
RUN echo "account default" > /etc/msmtprc && \
    echo "host mailhog" >> /etc/msmtprc && \
    echo "port 1025" >> /etc/msmtprc && \
    echo "from camagru@42.fr" >> /etc/msmtprc && \
    echo "aliases /etc/aliases" >> /etc/msmtprc

# 4. Dire à PHP d'utiliser msmtp pour la fonction mail()
RUN echo "sendmail_path = /usr/bin/msmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini

# 5. Active le module de réécriture d'URL (indispensable pour le Routeur)
RUN a2enmod rewrite

# 6. Change la racine du serveur vers le dossier 'public' (TRES IMPORTANT)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# 7. Applique ce changement dans les fichiers de config d'Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf