# Installation

Local setup. For a server, see the [Deployment Guide](./DEPLOYMENT.md).

## Requirements

- PHP 8.3
- MySQL 8.0+
- Node.js 22
- Composer
- Redis (optional, for caching)

## Setup

### 1. Clone and Install
```bash
git clone [repository-url]
cd metag-analyze
composer install
npm install
```

### 2. Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database
Create two databases (main and MART).

Update `.env` with the credentials, `DB_DATABASE` for the main database and `MART_DB_DATABASE` for the MART one.

Create the tables in both, the base roles and an administrator:
```bash
php artisan metag:install
```

### 4. Build Assets
```bash
npm run build
```

## Local Development

Using Laravel Herd or Valet for local HTTPS.

For frontend development:
```bash
npm run dev
```

## Troubleshooting

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
```

### Clear Caches
```bash
php artisan optimize:clear
```

### Database Reset (Local Only)
```bash
php artisan migrate:fresh-all --seed
```
This resets BOTH databases. Blocked in production.
