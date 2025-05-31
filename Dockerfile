FROM drupal:10-apache

# Install system dependencies for Drupal, git, composer, drush
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install intl mbstring pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Drush globally using Composer
RUN composer global require drush/drush \
    && ln -s /root/.composer/vendor/bin/drush /usr/local/bin/drush

# Copy and set permissions for entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

