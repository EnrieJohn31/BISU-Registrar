#!/bin/sh
set -eu
[ "${APP_ENV:-}" = testing ] && [ "${BISU_BROWSER_TESTS:-}" = 1 ]
[ "${DB_DATABASE:-}" = /var/www/html/storage/browser/database.sqlite ]

# Copy only source inputs: never .env, credentials, runtime data or the dev hot file.
tar -C /source --exclude=bootstrap/cache --exclude=public/build --exclude=public/hot \
    --exclude=public/storage --exclude='*.sqlite*' --exclude='*.sql' \
    -cf - app bootstrap config database public resources routes tests artisan \
    composer.json composer.lock package.json package-lock.json vite.config.ts \
    tsconfig.json tsconfig.browser.json playwright.config.ts | tar -C /var/www/html -xf -
mkdir -p bootstrap/cache storage/browser/auth storage/app/private storage/logs \
    storage/framework/cache/data storage/framework/sessions storage/framework/views
php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;' > storage/browser/app-key
read -r APP_KEY < storage/browser/app-key
export APP_KEY
composer install --no-interaction --prefer-dist
npm ci --no-audit --no-fund
npm run build
npm run types:browser
touch storage/browser/database.sqlite
php artisan migrate --force
php artisan db:seed --class=BrowserTestSeeder --force
