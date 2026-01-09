#!/bin/bash

# Exit on error
set -e

# Turn on bash's job control
set -m

echo "🚀 Starting Business Manager Backend Container..."

# Check if Laravel is installed by checking for artisan file
if [ ! -f "artisan" ]; then
    echo "⚡ Laravel not found. Installing fresh Laravel project..."
    
    echo "📦 Downloading Laravel to temporary directory..."
    # Install to /tmp/laravel first because /var/www contains Dockerfile/etc
    composer create-project laravel/laravel /tmp/laravel --prefer-dist --no-interaction
    
    echo "🚚 Moving Laravel files to project root..."
    # Copy all files, including hidden ones, from temp to current dir
    cp -r /tmp/laravel/. .
    
    # Clean up
    rm -rf /tmp/laravel
    
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
