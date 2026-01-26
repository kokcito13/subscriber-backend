# Docker Setup

This project uses Docker Compose to isolate all services (PHP, MySQL, Nginx) from your local machine.

## Quick Start

1. **Start all services:**
   ```bash
   docker-compose up -d
   ```

2. **Install dependencies (first time only):**
   ```bash
   docker-compose exec php composer install
   ```

3. **Create database:**
   ```bash
   docker-compose exec php php bin/console doctrine:database:create
   ```

4. **Access the application:**
   - Web: http://localhost:8080
   - Test endpoint: http://localhost:8080/test
   - MySQL: localhost:3306

## Useful Commands

### Start/Stop Services
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f database
```

### PHP/Symfony Commands
```bash
# Run Symfony console commands
docker-compose exec php php bin/console [command]

# Examples:
docker-compose exec php php bin/console about
docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console cache:clear
```

### Database Access
```bash
# Connect to MySQL
docker-compose exec database mysql -u app -p!ChangeMe! app

# Or using root
docker-compose exec database mysql -u root -prootpassword
```

### Composer Commands
```bash
# Install packages
docker-compose exec php composer require [package]

# Update packages
docker-compose exec php composer update
```

### Rebuild Containers
```bash
# Rebuild after Dockerfile changes
docker-compose build --no-cache

# Rebuild and restart
docker-compose up -d --build
```

## Services

- **nginx**: Web server (port 8080)
- **php**: PHP-FPM with Symfony (port 9000 internal)
- **database**: MySQL 8.0 (port 3306)

## Volumes

- `database_data`: Persistent MySQL data
- `php_data`: Symfony cache/logs

## Environment Variables

Default values are in `compose.yaml`. Override them in `compose.override.yaml` or use environment variables.
