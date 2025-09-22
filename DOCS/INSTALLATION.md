# Installation Guide

This guide provides complete installation instructions for Metag-Analyze, including all dependencies and third-party services.

## Prerequisites

- macOS, Linux, or Windows with WSL2
- PHP 8.3 or higher
- Composer 2.x
- Node.js 18+ and npm
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

# Configure database (see Database Setup below)
php artisan migrate

# Build frontend
npm run build

# Start development server
php artisan serve
```

## Development Environment Setup

### Option 1: Laravel Herd (Recommended for macOS)

1. **Download Laravel Herd**
   - Visit https://herd.laravel.com
   - Install the application
   - Herd provides PHP, Nginx, MySQL, Redis, and Node.js

2. **Configure Site**
   - Open Herd preferences
   - Add site pointing to `metag-analyze/public`
   - Access at `https://metag-analyze.test`

### Option 2: Laravel Valet (macOS/Linux)

1. **Install Valet**
   ```bash
   # macOS
   brew install php@8.3
   composer global require laravel/valet
   valet install

   # Linux
   apt-get install network-manager libnss3-tools jq xsel
   composer global require cpriego/valet-linux
   valet install
   ```

2. **Link Project**
   ```bash
   cd metag-analyze
   valet link metag-analyze
   # Access at https://metag-analyze.test
   ```

### Option 3: Manual Setup

1. **Install PHP 8.3+**
   ```bash
   # macOS
   brew install php@8.3

   # Ubuntu/Debian
   sudo apt install php8.3-cli php8.3-mysql php8.3-xml \
                    php8.3-mbstring php8.3-curl php8.3-zip

   # Windows WSL2
   sudo apt update && sudo apt install php8.3-cli php8.3-mysql \
                                       php8.3-xml php8.3-mbstring \
                                       php8.3-curl php8.3-zip
   ```

2. **Install Composer**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

3. **Install MySQL**
   ```bash
   # macOS
   brew install mysql
   brew services start mysql

   # Linux/WSL
   sudo apt install mysql-server
   sudo systemctl start mysql
   ```

4. **Install Redis**
   ```bash
   # macOS
   brew install redis
   brew services start redis

   # Linux
   sudo apt install redis-server
   sudo systemctl start redis
   ```

## Project Installation

### 1. Clone Repository

```bash
git clone https://github.com/your-org/metag-analyze.git
cd metag-analyze
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

1. **Create Database**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE metag_analyze;
   CREATE DATABASE metag_analyze_test; -- For testing
   exit;
   ```

2. **Configure Database in .env**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=metag_analyze
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate
   ```

4. **Seed Database (Optional)**
   ```bash
   # Basic data
   php artisan db:seed

   # Test data for development
   php artisan db:seed --class=MartProjectSeeder
   ```

### 5. Firebase Setup (Required for Push Notifications)

Firebase Cloud Messaging is required for sending push notifications to mobile devices.

1. **Create Firebase Project**
   - Go to https://console.firebase.google.com
   - Create new project or select existing
   - Navigate to Project Settings → Service Accounts

2. **Generate Service Account Key**
   - Click "Generate new private key"
   - Save the downloaded JSON file in project root
   - **Never commit this file to version control**

3. **Configure Firebase in .env**
   ```env
   FIREBASE_CREDENTIALS=./your-firebase-adminsdk.json
   FIREBASE_URL=https://your-project-id-default-rtdb.firebaseio.com/
   FCM_API_KEY=your_fcm_server_key
   ```

4. **Add to .gitignore**
   ```
   # Firebase credentials
   *firebase-adminsdk*.json
   ```

### 6. ALTCHA Setup (Spam Protection)

ALTCHA provides CAPTCHA-free spam protection for forms.

1. **Generate HMAC Key**
   ```bash
   # Generate a secure random key
   openssl rand -hex 32
   ```

2. **Configure in .env**
   ```env
   ALTCHA_HMAC_KEY=your_generated_hmac_key_here
   ```

3. **What It Protects**
   - User registration forms
   - Contact forms
   - Any public-facing forms

4. **Disable for Development (Optional)**
   ```env
   # Set to empty to disable ALTCHA in development
   ALTCHA_HMAC_KEY=
   ```

### 7. Additional Configuration

Configure these additional environment variables:

```env
# Application Settings
APP_NAME="Metag Analyze"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://metag-analyze.test

# Admin Configuration
ADMINS=admin@example.com,researcher@example.com
LINGUA_ADMIN=admin@example.com

# Build Configuration
VITE_ENV_MODE=development

# Security
BLOCKED_IPS=  # Comma-separated list of blocked IPs

# API Configuration
API_V2_CUTOFF_DATE=2025-03-21
FORCE_API_V2=true

# Study Limits
MAX_NUMBER_STUDIES=10

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### 8. Build Frontend Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Start Services

For development, you'll need to run these services:

```bash
# Terminal 1: Web server
php artisan serve

# Terminal 2: Queue worker (for background jobs)
php artisan queue:work

# Terminal 3: WebSocket server (for real-time features)
php artisan reverb:start

# Terminal 4: Frontend development (optional, for hot reload)
npm run dev
```

## Testing Installation

### 1. Verify PHP Installation
```bash
php -v
# Should show PHP 8.3.x or higher
```

### 2. Check Laravel
```bash
php artisan --version
# Should show Laravel Framework 11.x
```

### 3. Run Tests
```bash
# Run all tests
vendor/bin/pest

# Run specific test
vendor/bin/pest --filter=MartApiTest
```

### 4. Check Frontend Build
```bash
npm run build
# Should complete without errors
```

### 5. Access Application
- Open browser to https://metag-analyze.test (Herd/Valet)
- Or http://localhost:8000 (artisan serve)

## Troubleshooting

### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Database Connection Failed
- Verify MySQL is running: `mysql -u root -p`
- Check `.env` database credentials
- Ensure database exists: `SHOW DATABASES;`

### Composer Memory Limits
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
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

### ALTCHA Not Working
- Verify HMAC key is 64 characters (32 bytes hex)
- Check browser console for JavaScript errors
- Ensure frontend is properly built

### Redis Connection Failed
```bash
# Check Redis is running
redis-cli ping
# Should return PONG

# Verify Redis configuration in .env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Production Deployment

For production deployment:

1. **Set Environment**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize Application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   npm run build
   ```

3. **Set Up Queue Workers**
   Use Supervisor to keep queue workers running:
   ```ini
   [program:metag-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   numprocs=8
   ```

4. **Configure Web Server**
   - Point document root to `/public` directory
   - Enable HTTPS with SSL certificate
   - Configure proper file permissions

## Next Steps

After successful installation:

1. Create your first admin user
2. Configure mail settings for notifications
3. Set up your first research project
4. Review [ARCHITECTURE.md](./ARCHITECTURE.md) for system overview
5. Check [API Documentation](./API/) for mobile integration
6. See [CONFIGURATION.md](./REFERENCE/CONFIGURATION.md) for all environment variables