# Usar la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones de PHP necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache (opcional, útil para URLs amigables)
RUN a2enmod rewrite

# Copiar archivos de configuración personalizada si es necesario
# COPY php.ini /usr/local/etc/php/

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80