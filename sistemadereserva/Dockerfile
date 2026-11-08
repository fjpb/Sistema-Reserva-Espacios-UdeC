FROM php:8.2-apache

# Dependencias del sistema requeridas para las extensiones PHP y locales
RUN apt-get update && apt-get install -y --no-install-recommends \
    locales \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Generar locales necesarios (Español e Inglés)
RUN echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "es_ES.UTF-8 UTF-8" >> /etc/locale.gen \
    && echo "es_CL.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen

ENV LANG=es_CL.UTF-8 \
    LANGUAGE=es_CL:es \
    LC_ALL=es_CL.UTF-8

# Configurar e instalar extensiones PHP necesarias para MRBS (intl, pdo_mysql, mysqli, gd, zip)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        opcache

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Ajustes recomendados de PHP para MRBS
RUN { \
        echo 'date.timezone = America/Santiago'; \
        echo 'output_buffering = 4096'; \
        echo 'upload_max_filesize = 64M'; \
        echo 'post_max_size = 64M'; \
        echo 'memory_limit = 256M'; \
        echo 'max_execution_time = 300'; \
        echo 'display_errors = Off'; \
        echo 'display_startup_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
    } > /usr/local/etc/php/conf.d/mrbs-custom.ini

WORKDIR /var/www/html

# Copiar el código fuente como base
COPY mrbs/ /var/www/html/

# Permisos para el usuario web
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
