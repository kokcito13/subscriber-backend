# Subscription Backend

Symfony 8.0 application with MySQL database, running in Docker containers.

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Git

### Start the Application

**Option 1: Using the helper script (recommended)**
```bash
./docker-start.sh
```

**Option 2: Manual start**
```bash
# Start all services
docker-compose up -d

# Install dependencies (first time only)
docker-compose exec php composer install

# Create database
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
```

### Access the Application

- **Web Application**: http://localhost:8080
- **Test Endpoint**: http://localhost:8080/test
- **MySQL Database**: localhost:3306
  - Database: `app`
  - User: `app`
  - Password: `!ChangeMe!`
  - Root Password: `rootpassword`

## 🐳 Docker Services

The project runs three isolated Docker containers:

1. **nginx** - Web server (port 8080)
2. **php** - PHP 8.4-FPM with Symfony
3. **database** - MySQL 8.0 (port 3306)

All services are isolated from your local machine and won't interfere with local PHP/MySQL installations.

## 📝 Common Commands

### Docker Operations
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose build --no-cache
docker-compose up -d --build
```

### Symfony Console
```bash
# Run any Symfony command
docker-compose exec php php bin/console [command]

# Examples:
docker-compose exec php php bin/console about
docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console debug:router
```

### Composer
```bash
# Install package
docker-compose exec php composer require [package-name]

# Update dependencies
docker-compose exec php composer update
```

### Database
```bash
# Connect to MySQL
docker-compose exec database mysql -u app -p!ChangeMe! app

# Or as root
docker-compose exec database mysql -u root -prootpassword
```

## 🔧 Configuration

### Environment Variables

Default values are set in `compose.yaml`. You can override them:

1. Create `.env.local` file (not committed to git)
2. Or set environment variables before running `docker-compose`

Example `.env.local`:
```env
MYSQL_DATABASE=myapp
MYSQL_USER=myuser
MYSQL_PASSWORD=mypassword
MYSQL_PORT=3307
NGINX_PORT=8081
```

### Database Connection

The `DATABASE_URL` is automatically configured in the PHP container to connect to the MySQL service. No manual configuration needed!

## 📁 Project Structure

```
├── config/              # Symfony configuration
├── docker/              # Docker configuration files
│   ├── nginx/          # Nginx configuration
│   └── php/            # PHP Dockerfile
├── migrations/         # Database migrations
├── public/             # Web root
├── src/                # Application source code
│   ├── Controller/     # Controllers
│   ├── Entity/         # Doctrine entities
│   └── Repository/      # Repositories
├── compose.yaml        # Docker Compose configuration
└── docker-start.sh     # Quick start script
```

## 🧪 Testing

### Test the Setup
```bash
# Run the test script
docker-compose exec php php test-setup.php
```

### Test the API
```bash
# Using curl
curl http://localhost:8080/test

# Expected response:
# {"status":"success","message":"Symfony application is working!","timestamp":"..."}
```

## 🛠️ Development

### Adding New Dependencies
```bash
docker-compose exec php composer require [package-name]
```

### Creating Migrations
```bash
docker-compose exec php php bin/console make:migration
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### Clearing Cache
```bash
docker-compose exec php php bin/console cache:clear
```

## 📚 Documentation

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Docker Documentation](https://docs.docker.com/)
- [Doctrine ORM Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)

## 🔒 Security Notes

- Change default passwords in production!
- Never commit `.env.local` or sensitive files
- Review `compose.override.yaml` for local overrides

## 📄 License

Proprietary
