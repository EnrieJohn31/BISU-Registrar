#!/bin/sh
set -eu
[ "${APP_ENV:-}" = testing ] && [ "${BISU_BROWSER_TESTS:-}" = 1 ]
[ "${DB_DATABASE:-}" = /var/www/html/storage/browser/database.sqlite ]
read -r APP_KEY < storage/browser/app-key
export APP_KEY
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
