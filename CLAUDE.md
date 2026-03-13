# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Subscription Tracker** is a backend API powering an iOS app that helps users manage and track recurring subscriptions (streaming services, SaaS tools, apps, etc.). The goal is to give users visibility into recurring expenses and prevent forgotten charges.

The backend is a REST API built with **Symfony 8.0 / PHP 8.4**, using **MySQL 8.0** via Doctrine ORM. Runs in Docker (PHP-FPM + Nginx + MySQL).

### Multi-Kernel Setup
The project runs two Symfony kernels from the same codebase:
- **`www/`** — main API kernel (`Kernel`), serves `/v1/` subscription endpoints and `/api/auth`
- **`subscriber/`** — subscriber-facing web kernel (`SubscriberKernel`), separate cache dir (`var/cache/subscriber/`), routes defined in `config/routes_subscriber.yaml`, controllers in `src/Controller/Subscriber/`

## Commands

All commands run inside the Docker container:

```bash
# Start services
./docker-start.sh
# or
docker-compose up -d

# Install dependencies
docker-compose exec php composer install

# Database setup
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
docker-compose exec php php bin/console doctrine:migrations:migrate

# Clear cache (after config changes)
docker-compose exec php php bin/console cache:clear

# Generate API key for a user (CLI)
docker-compose exec php php bin/console app:manage-api-key <email>

# Run the test setup verification
docker-compose exec php php test-setup.php
```

Services: web at `http://localhost:8080`, MySQL at `localhost:3306` (user: `app`, pass: `!ChangeMe!`).

There are no automated PHPUnit tests in this project currently.

## Architecture

### Authentication Flow
1. `POST /api/auth` with `{"email": "..."}` creates or retrieves a user and returns a Bearer token.
2. All `/v1/*` routes require `Authorization: Bearer <token>`.
3. `TokenAuthenticator` (`src/Security/`) performs stateless auth: extracts the first 8 chars of the token as a prefix to find candidates in DB, then verifies the full token via `hash_equals` or `password_verify`.

### Request/Response Cycle
Controllers use **DTOs** in `src/Dto/` for both request deserialization+validation (`#[Assert\...]` attributes) and response serialization. Symfony's serializer/validator is injected into controllers.

### Billing Period Constraints
Validation is enforced in two places (DTO + entity lifecycle hooks):
- **weekly**: no `billing_day_of_month` or `billing_month_of_year`
- **monthly**: requires `billing_day_of_month`
- **yearly**: requires both `billing_day_of_month` and `billing_month_of_year`

### Key Source Directories
- `src/Controller/` — HTTP layer; thin controllers delegating to repositories/services
- `src/Entity/` — Doctrine entities with ORM attributes and lifecycle hooks (`#[ORM\PrePersist]`, `#[ORM\PreUpdate]`)
- `src/Dto/` — Request and response objects with validation constraints
- `src/Security/` — Custom `TokenAuthenticator` (stateless, Bearer token)
- `src/Repository/` — Doctrine repositories with query logic
- `src/EventSubscriber/` — `CorsSubscriber` handles permissive CORS (echoes `Origin` header, returns 204 for OPTIONS preflight)
- `config/packages/security.yaml` — Stateless firewall, no sessions

### Routing
Routes are defined via PHP 8 attributes on controller methods (`#[Route(...)]`) and auto-discovered. No separate routing YAML files for individual routes.

### Database
- Entities use UUID primary keys.
- Column naming: snake_case via Doctrine's `underscore_number_aware` strategy.
- Migrations live in `migrations/`; always run after entity changes.
- Web root is `www/` (not the default `public/`).
