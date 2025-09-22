# Backend Installation Guide

This guide provides step-by-step instructions for setting up the Laravel backend on your local development environment.

## Prerequisites

- macOS, Linux, or Windows with WSL2
- PHP 8.3 or higher
- Composer 2.x
- Node.js 18+ and npm
- Git

## Recommended Development Environment

### Option 1: Laravel Herd (Recommended for macOS)

Laravel Herd is the easiest way to get started on macOS.

1. **Download and Install Herd**
   ```bash
   # Download from https://herd.laravel.com
   # Install the app and follow the setup wizard
   ```

2. **Herd will automatically provide:**
   - PHP (multiple versions)
   - Nginx
   - MySQL/PostgreSQL
   - Redis
   - Node.js & npm
   - Composer

### Option 2: Laravel Valet (macOS/Linux)

For lightweight local development.

1. **Install Valet**
   ```bash
   # macOS
   brew install php
   composer global require laravel/valet
   valet install
   
   # Linux
   apt-get install network-manager libnss3-tools jq xsel
   composer global require cpriego/valet-linux
   valet install
   ```

2. **Install MySQL**
   ```bash
   # macOS
   brew install mysql
   brew services start mysql
   
   # Linux
   sudo apt install mysql-server
   sudo systemctl start mysql
   ```

### Option 3: Manual Installation

If you prefer manual setup or are on Windows.

1. **Install PHP 8.3+**
   ```bash
   # macOS
   brew install php@8.3

   # Ubuntu/Debian
   sudo apt install php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl

   # Windows (use WSL2)
   sudo apt update
   sudo apt install php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl
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
   sudo mysql_secure_installation
   ```

## Project Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-org/metag-analyze.git
   cd metag-analyze
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   # Copy the example environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

4. **Configure Database**
   
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=metag_analyze
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Create Database**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE metag_analyze;
   exit;
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Install Frontend Dependencies**
   ```bash
   npm install
   npm run build
   ```

8. **Set Up Storage Link**
   ```bash
   php artisan storage:link
   ```

9. **Configure Site URL (Herd/Valet)**
   
   **For Herd:**
   - Open Herd preferences
   - Add site pointing to `metag-analyze/public`
   - Access at `http://metag-analyze.test`

   **For Valet:**
   ```bash
   valet link metag-analyze
   # Access at http://metag-analyze.test
   ```

10. **Generate API Documentation** (Optional)
    ```bash
    php artisan l5-swagger:generate
    ```

## Required Services

### Redis (for queues and caching)

```bash
# macOS (Herd includes Redis)
brew install redis
brew services start redis

# Linux
sudo apt install redis-server
sudo systemctl start redis
```

### Queue Worker

For background jobs, run in a separate terminal:
```bash
php artisan queue:work
```

### WebSocket Server (for real-time features)

In another terminal:
```bash
php artisan reverb:start
```

## Additional Dependencies

### RTF Conversion Support

For RTF to HTML conversion:

```bash
# macOS
brew install --cask libreoffice
brew install unoconv

# Linux
sudo apt-get install libreoffice unoconv
```

## Verify Installation

1. **Check PHP Version**
   ```bash
   php -v
   # Should show PHP 8.3.x or higher
   ```

2. **Check Laravel Installation**
   ```bash
   php artisan --version
   # Should show Laravel Framework version
   ```

3. **Run Tests**
   ```bash
   vendor/bin/pest
   # All tests should pass
   ```

4. **Access Application**
   - Open browser to `http://metag-analyze.test` (Herd/Valet)
   - Or run `php artisan serve` and visit `http://localhost:8000`

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
# Clear npm cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

## Next Steps

- Set up Firebase configuration (see FIREBASE_SETUP.md)
- Set up your IDE with Laravel extensions
- Configure Xdebug for debugging (optional)
- Review the main [CLAUDE.md](../CLAUDE.md) for development guidelines
