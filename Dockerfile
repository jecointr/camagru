FROM php:8.1-apache

# Installation des extensions PHP nécessaires (PDO MySQL et GD pour les images)
RUN docker-php-ext-install pdo pdo_mysql gd

# Activation de l'URL rewriting pour le routeur MVC
RUN a2enmod rewrite

# Copie de la configuration Apache (optionnel si tu veux personnaliser)
# COPY config/apache.conf /etc/apache2/sites-available/000-default.conf