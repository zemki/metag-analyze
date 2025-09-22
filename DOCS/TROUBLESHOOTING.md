# Troubleshooting Guide

This guide covers common issues encountered when setting up and running Metag-Analyze.

## Installation Issues

### PHP/Composer Issues

**"No application encryption key has been specified"**
```bash
# Generate a new application key (ONLY on fresh installations!)
php artisan key:generate
```
**⚠️ WARNING:** Never run this on production with existing data!

**"Composer memory limit exceeded"**
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

**"PHP extension missing"**
```bash
# Check required extensions
php -m | grep -E "(mysql|xml|mbstring|curl|zip)"

# Install missing extensions (Ubuntu/Debian)
sudo apt install php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip

# Install missing extensions (macOS)
brew install php@8.3
```

### Database Issues

**"Access denied for user 'root'@'localhost'"**
```bash
# Reset MySQL root password
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';
FLUSH PRIVILEGES;
exit;
```

**"Database does not exist"**
```bash
mysql -u root -p
CREATE DATABASE metag_analyze;
CREATE DATABASE metag_analyze_test;
exit;
```

**"Connection refused (MySQL not running)"**
```bash
# Check MySQL status
sudo systemctl status mysql      # Linux
brew services list | grep mysql  # macOS

# Start MySQL
sudo systemctl start mysql       # Linux
brew services start mysql        # macOS
```

**"Too many connections"**
```sql
-- Check current connections
SHOW PROCESSLIST;

-- Check max connections
SHOW VARIABLES LIKE 'max_connections';

-- Increase max connections
SET GLOBAL max_connections = 200;
```

### Node.js/NPM Issues

**"Module not found" errors**
```bash
# Clear cache and reinstall
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

**"Permission denied" on npm install**
```bash
# Fix npm permissions (don't use sudo)
mkdir ~/.npm-global
npm config set prefix '~/.npm-global'
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
```

**"Node.js version incompatible"**
```bash
# Check Node version
node --version

# Use Node Version Manager
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
nvm use 18
```

## Runtime Issues

### Application Errors

**"500 Internal Server Error"**
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Enable debug mode in `.env`: `APP_DEBUG=true`
3. Check file permissions: `chmod -R 775 storage bootstrap/cache`
4. Clear application cache: `php artisan cache:clear`

**"Route not found" (404 errors)**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Check routes
php artisan route:list
```

**"Class not found" errors**
```bash
# Regenerate autoloader
composer dump-autoload

# Clear compiled classes
php artisan clear-compiled
```

### Performance Issues

**"Page loads slowly"**
1. Enable OpCache in production: `OPCACHE=true`
2. Cache configuration: `php artisan config:cache`
3. Cache routes: `php artisan route:cache`
4. Optimize Composer: `composer install --optimize-autoloader`

**"Memory limit exceeded"**
```bash
# Check current limit
php -ini | grep memory_limit

# Increase temporarily
php -d memory_limit=512M artisan command

# Increase permanently in php.ini
memory_limit = 512M
```

### Queue/Job Issues

**"Jobs not processing"**
```bash
# Check queue status
php artisan queue:work --timeout=60

# Clear failed jobs
php artisan queue:flush

# Restart queue workers
php artisan queue:restart
```

**"Queue worker memory leaks"**
```bash
# Use memory limit and restart
php artisan queue:work --memory=128 --timeout=60

# Set up supervisor for automatic restart
```

## Firebase Issues

### Connection Problems

**"Firebase credentials file not found"**
1. Check file path: `ls -la ./your-firebase-adminsdk.json`
2. Verify FIREBASE_CREDENTIALS in `.env`
3. Ensure file is readable: `chmod 644 firebase-file.json`

**"Firebase authentication failed"**
1. Re-download service account key from Firebase Console
2. Verify project ID matches Firebase project
3. Check file is valid JSON: `cat firebase-file.json | jq .`

**"FCM push notifications not working"**
1. Verify FCM_API_KEY in `.env`
2. Check mobile app registration tokens
3. Test with Firebase Console messaging
4. Review FCM quotas and limits

### Firebase Configuration

**"Invalid database URL"**
```env
# Correct format
FIREBASE_URL=https://your-project-id-default-rtdb.firebaseio.com/

# Common mistakes to avoid
FIREBASE_URL=https://your-project-id.firebaseio.com/        # Missing -default-rtdb
FIREBASE_URL=https://your-project-id-default-rtdb.firebaseio.com   # Missing trailing slash
```

## API Issues

### MART API Problems

**"Unauthenticated" (401 errors)**
1. Check Authorization header: `Authorization: Bearer [token]`
2. Verify token in database: `SELECT api_token FROM users WHERE email = 'mobile@test.com'`
3. Token must be hashed: `hash('sha256', 'your_token')`

**"Case is completed" (422 errors)**
```sql
-- Check case status
SELECT id, name, status FROM cases WHERE id = 5;

-- Reactivate case if needed
UPDATE cases SET status = 'active' WHERE id = 5;
```

**"Validation failed" errors**
1. Check required fields in request body
2. Verify data types (integers for radio, arrays for checkbox)
3. Ensure questionnaire_id exists
4. Check answer values are within valid ranges

### API Performance

**"API requests timeout"**
1. Check database query performance
2. Add indexes for frequently queried fields
3. Enable query logging: `DB_LOG_QUERIES=true`
4. Use eager loading to prevent N+1 queries

## Security Issues

### ALTCHA Problems

**"ALTCHA validation failed"**
1. Verify HMAC key is 64 characters: `echo $ALTCHA_HMAC_KEY | wc -c`
2. Regenerate key: `openssl rand -hex 32`
3. Check browser console for JavaScript errors
4. Ensure frontend build includes ALTCHA library

### IP Blocking Issues

**"Access denied" for legitimate users**
1. Check BLOCKED_IPS in `.env`
2. Remove IP from blocked list
3. Consider using fail2ban instead for automated blocking

## Development Environment

### Laravel Herd Issues

**"Site not accessible"**
1. Check Herd is running
2. Verify site configuration points to `/public` directory
3. Check SSL certificate is valid
4. Try accessing via IP: `http://127.0.0.1`

### Laravel Valet Issues

**"Site not found"**
```bash
# Re-link site
valet link metag-analyze

# Restart Valet
valet restart

# Check Valet status
valet status
```

**"SSL certificate issues"**
```bash
# Regenerate certificates
valet secure metag-analyze
valet restart
```

### File Permission Issues

**"Permission denied" errors**
```bash
# Fix Laravel permissions
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Fix uploaded files permissions
chmod -R 755 storage/app/uploads
```

## Testing Issues

### Test Database Problems

**"Tests use production database"**
1. Create `.env.testing` file
2. Set test database: `DB_DATABASE=metag_analyze_test`
3. Create test database: `mysql -e "CREATE DATABASE metag_analyze_test"`

**"Tests fail with authentication errors"**
```bash
# Clear test cache
php artisan config:clear --env=testing
php artisan cache:clear --env=testing

# Run specific test
vendor/bin/pest --filter=MartApiTest
```

## Production Issues

### SSL/HTTPS Problems

**"Mixed content" warnings**
1. Ensure APP_URL uses https
2. Force HTTPS in AppServiceProvider
3. Check all asset URLs use HTTPS

**"SSL certificate expired"**
1. Renew certificate with your provider
2. Update server configuration
3. Restart web server

### Performance Optimization

**"High server load"**
1. Enable caching: `CACHE_DRIVER=redis`
2. Use queue workers: `QUEUE_CONNECTION=redis`
3. Optimize database queries
4. Enable opcache: `OPCACHE=true`

### Backup and Recovery

**"Database corruption"**
```bash
# Check table integrity
mysqlcheck -u root -p metag_analyze

# Repair tables if needed
mysqlcheck -u root -p --auto-repair metag_analyze

# Restore from backup
mysql -u root -p metag_analyze < backup_file.sql
```

## Logging and Debugging

### Enable Debug Logging

```env
# .env settings for debugging
APP_DEBUG=true
LOG_LEVEL=debug
DB_LOG_QUERIES=true
```

### Common Log Locations

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/error.log   # Nginx
tail -f /var/log/apache2/error.log # Apache

# System logs
journalctl -f -u mysql             # MySQL (systemd)
```

### Database Query Debugging

```php
// Enable query logging in AppServiceProvider
use Illuminate\Support\Facades\DB;

DB::listen(function ($query) {
    Log::info('Query: ' . $query->sql);
    Log::info('Bindings: ' . implode(', ', $query->bindings));
    Log::info('Time: ' . $query->time . 'ms');
});
```

## Getting Help

### Before Asking for Help

1. Check Laravel logs: `storage/logs/laravel.log`
2. Try clearing caches: `php artisan cache:clear`
3. Verify environment configuration
4. Test with minimal reproducible example

### Useful Commands for Diagnostics

```bash
# System information
php --version
composer --version
node --version
mysql --version

# Laravel information
php artisan --version
php artisan env
php artisan config:show app

# Check services
sudo systemctl status mysql nginx redis
```

### When Reporting Issues

Include:
1. PHP and Laravel versions
2. Operating system
3. Error messages (full stack trace)
4. Steps to reproduce
5. Environment configuration (sanitized)
6. Recent changes made to the system