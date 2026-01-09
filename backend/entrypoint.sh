#!/bin/bash

# Exit on error
set -e

# Turn on bash's job control
set -m

echo "🚀 Starting Business Manager Backend Container..."

# Check if Laravel is installed checks for artisan file
if [ ! -f "artisan" ]; then
    echo "⚡ Laravel not found. Installing fresh Laravel project..."
    
    # Remove empty hidden files if any, to allow composer create-project to work if dir is technically not empty but has system files
    # Being safe: only run create-project if directory is effectively empty or contains only docker stuff
    
    # We will use composer create-project in a temp folder and move it, 
    # OR just use composer create-project . if empty.
    # Current dir is /var/www
    
    # To avoid "directory not empty" errors if simple files exist, we can use a force install method
    # or install to a temp dir and move.
    
    echo "📦 Downloading Laravel..."
    composer create-project laravel/laravel . --prefer-dist --no-interaction
    
    echo "🔧 Setting permissions..."
    chown -R www-data:www-data /var/www
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache
    
    echo "✅ Laravel installed successfully!"
else
    echo "✅ Laravel already installed."
    
    # Ensure dependencies are installed if vendor is missing but composer.json exists
    if [ ! -d "vendor" ] && [ -f "composer.json" ]; then
        echo "📦 Installing dependencies..."
        composer install --no-interaction --optimize-autoloader
    fi
fi

# Configuration checks
if [ -f ".env.example" ] && [ ! -f ".env" ]; then
    echo "⚙️ Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Wait for MySQL (simple wait)
echo "⏳ Waiting for Database connection..."
sleep 10

# Run migrations if database is ready
echo "🔄 Running migrations..."
php artisan migrate --force || echo "⚠️ Migration failed. Check database connection."

# Start PHP-FPM
echo "🏁 Starting PHP-FPM..."
php-fpm
