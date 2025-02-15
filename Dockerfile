FROM php:8.3-fpm

# Definir nome do usuário e UID
ARG user=joaomarcosns
ARG uid=1000

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev  # Adiciona suporte ao PostgreSQL

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema para rodar Composer e Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Instalar Redis
RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar configurações personalizadas do PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

USER $user
