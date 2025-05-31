#!/bin/bash
set -e

echo "[INFO] Running custom entrypoint..."

# Fix permissions (if needed)
chown -R www-data:www-data /var/www/html

# Move into the Drupal project directory
cd /var/www/html

# If vendor dir is missing, run composer install
if [ ! -d "vendor" ]; then
    echo "[INFO] Running composer install..."
    composer install
fi

# If settings.php is missing, create it from default
if [ ! -f "web/sites/default/settings.php" ]; then
    echo "[INFO] Copying default settings.php..."
    cp web/sites/default/default.settings.php web/sites/default/settings.php
    chmod 644 web/sites/default/settings.php
    chown www-data:www-data web/sites/default/settings.php
fi

# Run database install if not already installed
if [ ! -f "web/sites/default/files/install.log" ]; then
    echo "[INFO] Installing Drupal site using Drush..."
    drush site:install standard \
      --db-url=mysql://${DRUPAL_DB_USER}:${DRUPAL_DB_PASSWORD}@${DRUPAL_DB_HOST}/${DRUPAL_DB_NAME} \
      --account-name=admin \
      --account-pass=admin \
      --yes \
      && touch web/sites/default/files/install.log
fi

echo "[INFO] Entrypoint complete. Starting Apache..."
exec "$@"
