#!/bin/bash

# Docker helper script for Symfony project

set -e

echo "🐳 Starting Docker services..."

# Start services
docker-compose up -d

echo "⏳ Waiting for services to be ready..."
sleep 5

# Check if composer dependencies are installed
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    docker-compose exec -T php composer install
fi

# Clear cache to ensure everything is fresh
echo "🧹 Clearing Symfony cache..."
docker-compose exec -T php php bin/console cache:clear || echo "Cache clear failed (this is OK on first run)"

# Check database connection and create if needed
echo "🗄️  Setting up database..."
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists || echo "Database might already exist or connection failed"

echo ""
echo "✅ Services are running!"
echo ""
echo "📍 Access points:"
echo "   - Web application: http://localhost:8080"
echo "   - Test endpoint:   http://localhost:8080/test"
echo "   - MySQL:           localhost:3306"
echo ""
echo "📝 Useful commands:"
echo "   - View logs:        docker-compose logs -f"
echo "   - Stop services:    docker-compose down"
echo "   - Symfony console:  docker-compose exec php php bin/console [command]"
echo ""
