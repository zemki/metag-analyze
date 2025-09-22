# Production Deployment Guide

This guide covers deploying Metag-Analyze to production environments.

## Prerequisites

### Server Requirements

- **Operating System**: Ubuntu 20.04+ LTS or CentOS 8+
- **PHP**: 8.3+ with required extensions
- **Web Server**: Nginx (recommended) or Apache 2.4+
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Process Manager**: Supervisor for queue workers
- **SSL Certificate**: Required for HTTPS

### Resource Requirements

- **CPU**: 2+ cores recommended
- **RAM**: 4GB minimum, 8GB+ recommended
- **Storage**: 50GB+ SSD recommended
- **Network**: Reliable internet for Firebase/external services

## Server Setup

### 1. Install PHP 8.3 and Extensions

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-xml \
                 php8.3-mbstring php8.3-curl php8.3-zip \
                 php8.3-redis php8.3-opcache php8.3-gd

# CentOS/RHEL
sudo dnf install php83 php83-php-fpm php83-php-mysql php83-php-xml \
                 php83-php-mbstring php83-php-curl php83-php-zip \
                 php83-php-redis php83-php-opcache php83-php-gd
```

### 2. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 3. Install Node.js 18+

```bash
# Using NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version
```

### 4. Install MySQL 8.0

```bash
# Ubuntu/Debian
sudo apt install mysql-server-8.0

# Secure installation
sudo mysql_secure_installation

# Create databases
sudo mysql
CREATE DATABASE metag_analyze;
CREATE USER 'metag_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON metag_analyze.* TO 'metag_user'@'localhost';
FLUSH PRIVILEGES;
exit;
```

### 5. Install Redis

```bash
# Ubuntu/Debian
sudo apt install redis-server

# Start and enable
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Verify
redis-cli ping  # Should return PONG
```

### 6. Install Nginx

```bash
# Ubuntu/Debian
sudo apt install nginx

# Start and enable
sudo systemctl start nginx
sudo systemctl enable nginx
```

## Application Deployment

### 1. Create Application Directory

```bash
sudo mkdir -p /var/www/metag-analyze
sudo chown $USER:www-data /var/www/metag-analyze
```

### 2. Clone and Setup Application

```bash
cd /var/www/metag-analyze

# Clone repository
git clone https://github.com/your-org/metag-analyze.git .

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci --only=production

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 3. Environment Configuration

```bash
# Copy and configure environment
cp .env.example .env

# Edit .env with production settings
nano .env
```

**Production .env Settings:**
```env
APP_NAME="Metag Analyze"
APP_ENV=production
APP_KEY=base64:YOUR_32_CHARACTER_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=metag_analyze
DB_USERNAME=metag_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls

# Firebase (for push notifications)
FIREBASE_CREDENTIALS=/var/www/metag-analyze/firebase-prod.json
FIREBASE_URL=https://your-project-prod-default-rtdb.firebaseio.com/
FCM_API_KEY=your_fcm_server_key

# Security
ALTCHA_HMAC_KEY=your_64_character_hmac_key
ADMINS=admin@your-domain.com
BLOCKED_IPS=

# Performance
OPCACHE=true
CACHE_VIEWS=true
```

### 4. Database Migration

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --class=AdminSeeder
```

### 5. Build Assets

```bash
# Build production assets
npm run build

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. File Storage Setup

```bash
# Create storage directories
mkdir -p storage/app/uploads
mkdir -p storage/app/public

# Create symbolic link
php artisan storage:link

# Set permissions
chmod -R 755 storage/app/uploads
```

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/metag-analyze`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/metag-analyze/public;

    index index.php index.html;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Client uploads
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Security
        fastcgi_param HTTP_PROXY "";
        fastcgi_param SERVER_NAME $host;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security
    location ~ /\.ht {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    location /storage {
        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/metag-analyze /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### PHP-FPM Configuration

Edit `/etc/php/8.3/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Security
php_admin_value[disable_functions] = exec,passthru,shell_exec,system
php_admin_flag[allow_url_fopen] = off
```

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
; Performance
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=20000
opcache.revalidate_freq=2
opcache.fast_shutdown=1

; Security
expose_php=Off
max_execution_time=300
max_input_time=300
memory_limit=512M
post_max_size=50M
upload_max_filesize=50M

; Session
session.cookie_httponly=1
session.cookie_secure=1
session.use_strict_mode=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

## Queue Workers with Supervisor

Install Supervisor:
```bash
sudo apt install supervisor
```

Create `/etc/supervisor/conf.d/metag-worker.conf`:

```ini
[program:metag-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/metag-analyze/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/metag-analyze/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start metag-worker:*
```

## SSL Certificate Setup

### Using Let's Encrypt (Recommended)

```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (already configured by default)
sudo systemctl status certbot.timer
```

### Using Custom Certificate

1. Upload certificate files to server
2. Update Nginx configuration with certificate paths
3. Test configuration: `sudo nginx -t`
4. Reload Nginx: `sudo systemctl reload nginx`

## Monitoring and Maintenance

### Log Management

```bash
# Rotate Laravel logs
echo '/var/www/metag-analyze/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.3-fpm
    endscript
}' | sudo tee /etc/logrotate.d/metag-analyze
```

### Backup Strategy

**Database Backup Script** (`/usr/local/bin/backup-metag.sh`):
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/metag-analyze"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u metag_user -p'secure_password' metag_analyze | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C /var/www/metag-analyze storage/app/uploads

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable and add to cron:
```bash
sudo chmod +x /usr/local/bin/backup-metag.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-metag.sh
```

### Health Monitoring

**Health Check Script** (`/usr/local/bin/health-check-metag.sh`):
```bash
#!/bin/bash
DOMAIN="https://your-domain.com"

# Check application health
if curl -s -o /dev/null -w "%{http_code}" $DOMAIN/health | grep -q "200"; then
    echo "✓ Application healthy"
else
    echo "✗ Application error" | mail -s "Metag-Analyze Down" admin@your-domain.com
fi

# Check queue workers
if supervisorctl status metag-worker:* | grep -q "RUNNING"; then
    echo "✓ Queue workers running"
else
    echo "✗ Queue workers stopped" | mail -s "Queue Workers Down" admin@your-domain.com
    supervisorctl restart metag-worker:*
fi
```

### Performance Monitoring

**Key Metrics to Monitor:**
- Response times (< 200ms for API endpoints)
- Queue job processing time
- Database query performance
- Memory usage
- Disk space usage
- SSL certificate expiration

## Deployment Automation

### Deploy Script

Create `/usr/local/bin/deploy-metag.sh`:

```bash
#!/bin/bash
set -e

APP_DIR="/var/www/metag-analyze"
BACKUP_DIR="/var/backups/metag-analyze"
DATE=$(date +%Y%m%d_%H%M%S)

echo "Starting deployment: $DATE"

cd $APP_DIR

# Backup before deployment
mkdir -p $BACKUP_DIR
mysqldump -u metag_user -p'secure_password' metag_analyze > $BACKUP_DIR/pre_deploy_$DATE.sql

# Put app in maintenance mode
php artisan down --render="errors::503"

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm ci --only=production

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
supervisorctl restart metag-worker:*

# Restart PHP-FPM
systemctl reload php8.3-fpm

# Bring app back up
php artisan up

echo "Deployment completed: $DATE"
```

### Zero-Downtime Deployment

For zero-downtime deployments, consider:
1. Blue-green deployment strategy
2. Load balancer with multiple app servers
3. Database migration compatibility checks
4. Feature flags for new functionality

## Security Checklist

### Server Security

- [ ] Update system packages regularly
- [ ] Configure firewall (UFW/iptables)
- [ ] Disable root SSH login
- [ ] Use SSH key authentication
- [ ] Install fail2ban for brute force protection
- [ ] Enable automatic security updates

### Application Security

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Secure Firebase credentials file
- [ ] Configure ALTCHA for spam protection
- [ ] Set up IP blocking if needed
- [ ] Use HTTPS everywhere
- [ ] Configure security headers
- [ ] Regular dependency updates

### Monitoring

- [ ] Set up log monitoring
- [ ] Configure application health checks
- [ ] Monitor SSL certificate expiration
- [ ] Set up alerts for critical errors
- [ ] Monitor queue worker status

## Troubleshooting Deployment

### Common Issues

**"Permission denied" errors**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**"Queue workers not processing"**
```bash
supervisorctl status metag-worker:*
supervisorctl restart metag-worker:*
```

**"Database connection failed"**
1. Check MySQL is running: `systemctl status mysql`
2. Verify credentials in `.env`
3. Test connection: `mysql -u metag_user -p metag_analyze`

**"SSL certificate errors"**
1. Check certificate validity: `openssl x509 -in certificate.crt -text -noout`
2. Verify certificate chain
3. Check Nginx configuration: `nginx -t`

For more troubleshooting help, see [TROUBLESHOOTING.md](./TROUBLESHOOTING.md).