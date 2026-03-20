# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repo contains two distinct products sharing one Symfony 8.0 / PHP 8.4 codebase:

1. **Subscription Tracker** — REST API powering an iOS app for managing recurring subscriptions.
2. **Baby Rhythm** — AI-powered baby sleep tracker with a public marketing landing page.

Persistence: MySQL 8.0 via Doctrine ORM. Runs in Docker (PHP-FPM + Nginx + MySQL).

---

## Multi-Kernel Setup

Three Symfony kernels share the same `src/`, `config/`, `templates/`, and `vendor/` directories:

| Kernel class | Web root | Console binary | Cache dir | Route config |
|---|---|---|---|---|
| `Kernel` | `www/` | `bin/console` | `var/cache/{env}/` | `config/routes.yaml` |
| `SubscriberKernel` | `subscriber/` | `bin/console-subscriber` | `var/cache/subscriber/{env}/` | `config/routes_subscriber.yaml` |
| `BabyRhythmKernel` | `baby-rhythm/` | `bin/console-baby-rhythm` | `var/cache/baby-rhythm/{env}/` | `config/routes_baby_rhythm.yaml` |

Each kernel has its own log dir (`var/log/`, `var/log/subscriber/`, `var/log/baby-rhythm/`).

Route configs load controllers by namespace+directory using `type: attribute`:
- `routes_subscriber.yaml` → `src/Controller/Subscriber/`
- `routes_baby_rhythm.yaml` → `src/Controller/BabyRhythm/`

**Cache must be cleared per kernel** after any config, route, or service change.

---

## Commands

All commands run inside the Docker container:

```bash
# Start services
./docker-start.sh   # or: docker-compose up -d

# Install dependencies
docker-compose exec php composer install

# Database setup
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
docker-compose exec php php bin/console doctrine:migrations:migrate

# Clear + warm cache — run for every affected kernel
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console-subscriber cache:clear
docker-compose exec php php bin/console-baby-rhythm cache:clear

# Generate API key for a user
docker-compose exec php php bin/console app:manage-api-key <email>
```

Services: web at `http://localhost:8080`, MySQL at `localhost:3306` (user: `app`, pass: `!ChangeMe!`).

There are no automated PHPUnit tests currently.

---

## Architecture

### Main API Kernel (`www/`)

**Auth flow:**
1. `POST /api/auth` with `{"email": "..."}` → creates/retrieves user → returns Bearer token.
2. All `/v1/*` routes require `Authorization: Bearer <token>`.
3. `TokenAuthenticator` (`src/Security/`) is stateless: extracts first 8 chars of token as a DB lookup prefix, then verifies with `hash_equals` / `password_verify`.

**Request/Response cycle:**
Controllers are thin. They deserialize the request body into a DTO (`src/Dto/`), run Symfony validation (`#[Assert\...]` attributes), delegate to a repository or service, then return a serialized DTO response.

**Billing period constraints** — validated in both the DTO and entity `#[ORM\PrePersist]` / `#[ORM\PreUpdate]` hooks:
- `weekly` → no `billing_day_of_month` or `billing_month_of_year`
- `monthly` → requires `billing_day_of_month`
- `yearly` → requires both fields

**CORS:** `CorsSubscriber` (`src/EventSubscriber/`) echoes `Origin` and returns 204 for OPTIONS preflight — no CORS package used.

### Subscriber & Baby Rhythm Kernels

Both are Twig-rendered marketing/web kernels with no API auth. They extend `BaseKernel` with `MicroKernelTrait` and override `getCacheDir()`, `getLogDir()`, and `configureRoutes()`.

- **Subscriber** (`src/Controller/Subscriber/`) — privacy policy and subscriber home page.
- **Baby Rhythm** (`src/Controller/BabyRhythm/`) — public App Store marketing landing page. Templates live in `templates/baby-rhythm/landing/` as Twig partials (`_hero`, `_features`, `_how_it_works`, `_footer`). CSS at `baby-rhythm/css/landing.css` (served from the kernel's web root). The landing page passes `app_name`, `app_tagline`, and `download_url` variables from the controller.

### Database

- UUID primary keys on all entities.
- Column naming: snake_case via Doctrine's `underscore_number_aware` strategy.
- Migrations in `migrations/`; always run after entity changes.

### Deployment

CI/CD via `.github/workflows/php.yml` — deploys on push to `main` via rsync over SSH, then runs `cache:clear` + `cache:warmup` + `doctrine:migrations:migrate` for all three kernels on the server.
