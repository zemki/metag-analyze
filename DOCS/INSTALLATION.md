# Installation

## Requirements

- PHP 8.3+
- MySQL 8.0+
- Node.js 18+
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

Update `.env` with database credentials.

Run migrations:
```bash
php artisan migrate
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
