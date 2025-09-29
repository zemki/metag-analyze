# Installation Guide

## Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 18+
- MySQL 8.0+
- Redis (for queues and caching)
- Git

## Quick Start

```bash
# Clone repository
git clone https://github.com/your-org/metag-analyze.git
cd metag-analyze

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database
php artisan migrate

# Build frontend
npm run build
```

## Development Environment

### Option 1: Laravel Herd (Recommended for macOS)

1. Download from https://herd.laravel.com
2. Add site pointing to `metag-analyze/public`
3. Access at `https://metag-analyze.test`

### Option 2: Laravel Valet (macOS/Linux)

```bash
# Install Valet
composer global require laravel/valet
valet install

# Link project
cd metag-analyze
valet link metag-analyze
```

### Option 3: Manual Setup

```bash
# Install PHP 8.3+
brew install php@8.3  # macOS
sudo apt install php8.3-cli php8.3-mysql php8.3-xml  # Ubuntu

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
brew install mysql  # macOS
sudo apt install mysql-server  # Ubuntu

# Install Redis
brew install redis  # macOS
sudo apt install redis-server  # Ubuntu
```

## Configuration

### 1. Database Setup

```bash
# Create databases
mysql -u root -p
CREATE DATABASE metag_analyze;
CREATE DATABASE metag_analyze_test;  # For testing
exit;

# Configure in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=metag_analyze
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations
php artisan migrate
```

### 2. Firebase Setup (Push Notifications)

1. Go to https://console.firebase.google.com
2. Create project or select existing
3. Navigate to Project Settings → Service Accounts
4. Generate new private key
5. Save JSON file in project root

```env
FIREBASE_CREDENTIALS=./your-firebase-adminsdk.json
FIREBASE_URL=https://your-project-id-default-rtdb.firebaseio.com/
FCM_API_KEY=your_fcm_server_key
```

**Important:** Add `*firebase-adminsdk*.json` to `.gitignore`

### 3. ALTCHA Setup (Spam Protection)

```bash
# Generate HMAC key
openssl rand -hex 32
```

```env
ALTCHA_HMAC_KEY=your_generated_64_char_key
```

### 4. Environment Variables

```env
# Application
APP_NAME="Metag Analyze"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://metag-analyze.test

# Admin
ADMINS=admin@example.com,researcher@example.com
LINGUA_ADMIN=admin@example.com

# API
API_V2_CUTOFF_DATE=2025-03-21
FORCE_API_V2=true

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

# Cache/Queue
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Limits
MAX_NUMBER_STUDIES=10
BLOCKED_IPS=  # Comma-separated IPs to block
```

### 5. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

## Production Deployment

### 1. Server Setup

```bash
# Install PHP 8.3 and extensions
sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-xml \
                 php8.3-mbstring php8.3-curl php8.3-zip \
                 php8.3-redis php8.3-opcache

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install MySQL 8.0
sudo apt install mysql-server-8.0
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server

```

### 2. Deploy Application

```bash
# Clone repository
cd /var/www
git clone https://github.com/your-org/metag-analyze.git
cd metag-analyze

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Configure environment
cp .env.example .env
# Edit .env with production values
# APP_ENV=production
# APP_DEBUG=false

# Generate key (only on fresh install!)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Cron Jobs

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/metag-analyze && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Failed

```bash
# Check MySQL is running
systemctl status mysql

# Verify credentials
mysql -u root -p

# Check database exists
SHOW DATABASES;
```

### NPM Installation Issues

```bash
# Clear cache and reinstall
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

### Firebase Connection Issues

- Verify service account JSON file exists
- Check file path in `FIREBASE_CREDENTIALS`
- Ensure project ID matches Firebase console

### Common Laravel Errors

**"No application encryption key"**
```bash
php artisan key:generate  # ONLY on fresh install!
```

**"Class not found"**
```bash
composer dump-autoload
```

**"Route cache error"**
```bash
php artisan route:clear
php artisan route:cache
```

**"View not found"**
```bash
php artisan view:clear
php artisan view:cache
```

## Testing Installation

```bash
# Run backend tests
vendor/bin/pest

# Check frontend build
npm run build
npm run lint:check
npm run format:check

# Verify application
curl -I https://metag-analyze.test
```

## Next Steps

1. Create admin user account
2. Configure mail settings
3. Set up first research project
4. Review [Architecture](./ARCHITECTURE.md) for system overview
