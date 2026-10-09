#!/bin/sh
set -e

cd /var/www/html

# Install PHP dependencies (bind mount wipes what was installed during
# image build). Re-runs only when composer.lock changed since the last
# install, so a `git pull` that adds a package doesn't leave vendor/ out
# of date, while normal restarts skip this slow step.
lock_hash=$(md5sum composer.lock | cut -d' ' -f1)
if [ ! -d vendor ] || [ "$(cat vendor/.lock-hash 2>/dev/null)" != "$lock_hash" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader
    echo "$lock_hash" > vendor/.lock-hash
fi

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate app key if missing
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    echo "Generating app key..."
    php artisan key:generate --force
fi

# Create SQLite database file if missing
mkdir -p database
if [ ! -f database/database.sqlite ]; then
    echo "Creating SQLite database file..."
    touch database/database.sqlite
fi

# Ensure required storage directories exist
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/testing \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# Fix ownership/permissions every start (cheap, and covers files
# that got created as root before this script ran, e.g. by an
# earlier manual `touch` or a host-side tool)
chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed only once, tracked with a marker file so restarts don't
# re-seed and duplicate data
if [ ! -f storage/.seeded ]; then
    echo "Seeding database..."
    php artisan db:seed --force
    touch storage/.seeded
fi

# Self-signed certificate used to sign the simulated NFC-e (no-op if it exists)
php artisan fiscal:test-certificate
chown -R www-data:www-data storage/app 2>/dev/null || true

echo "Setup complete. Starting php-fpm..."
exec "$@"
