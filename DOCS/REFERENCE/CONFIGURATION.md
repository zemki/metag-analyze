# Environment Configuration Reference

This document describes all environment variables used in Metag-Analyze. Copy `.env.example` to `.env` and configure these values.

## Application Settings

### Core Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `APP_NAME` | string | "Metag Analyze" | Application display name |
| `APP_ENV` | string | local | Environment (local/staging/production) |
| `APP_KEY` | string | - | **Required** - Encryption key (generate with `php artisan key:generate`) |
| `APP_DEBUG` | boolean | true | Enable debug mode (set to false in production) |
| `APP_URL` | string | http://localhost | Application URL |

### API Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `API_V2_CUTOFF_DATE` | date | 2025-03-21 | Date when API v2 becomes mandatory |
| `FORCE_API_V2` | boolean | false | Force all clients to use API v2 |

## Database Configuration

### MySQL Connection

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `DB_CONNECTION` | string | mysql | Database driver |
| `DB_HOST` | string | 127.0.0.1 | Database host |
| `DB_PORT` | integer | 3306 | Database port |
| `DB_DATABASE` | string | metag | Database name |
| `DB_USERNAME` | string | root | Database username |
| `DB_PASSWORD` | string | - | Database password |

### Test Database

For testing, create a `.env.testing` file with:
```env
DB_DATABASE=metag_analyze_test
```

## Firebase Configuration

Firebase is required for push notifications to mobile devices.

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `FIREBASE_CREDENTIALS` | string | - | Path to Firebase service account JSON file |
| `FIREBASE_URL` | string | - | Firebase database URL (format: https://PROJECT-ID-default-rtdb.firebaseio.com/) |
| `FCM_API_KEY` | string | - | Firebase Cloud Messaging server key |

### Example Configuration
```env
FIREBASE_CREDENTIALS=./metag-firebase-adminsdk.json
FIREBASE_URL=https://metag-69e35-default-rtdb.firebaseio.com/
FCM_API_KEY=AAAABp3qfr8:APA91bFMcDE0q3gqC5sz...
```

## Cache and Queue Configuration

### Cache Driver

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `CACHE_DRIVER` | string | file | Cache backend (file/redis/memcached) |

### Queue Connection

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `QUEUE_CONNECTION` | string | sync | Queue driver (sync/database/redis) |
| `QUEUE_RETRY_AFTER` | integer | 90 | Seconds before retry |

### Redis Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `REDIS_HOST` | string | 127.0.0.1 | Redis server host |
| `REDIS_PASSWORD` | string | null | Redis password (if required) |
| `REDIS_PORT` | integer | 6379 | Redis server port |

## Session Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `SESSION_DRIVER` | string | database | Session storage (file/database/redis) |
| `SESSION_LIFETIME` | integer | 120 | Session lifetime in minutes |

## Mail Configuration

Required for sending notification emails.

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `MAIL_MAILER` | string | smtp | Mail driver (smtp/sendmail/mailgun) |
| `MAIL_HOST` | string | - | SMTP server host |
| `MAIL_PORT` | integer | 587 | SMTP server port |
| `MAIL_USERNAME` | string | - | SMTP username |
| `MAIL_PASSWORD` | string | - | SMTP password |
| `MAIL_ENCRYPTION` | string | tls | Encryption method (tls/ssl/null) |
| `MAIL_FROM_ADDRESS` | string | - | Default "from" email address |
| `MAIL_FROM_NAME` | string | ${APP_NAME} | Default "from" name |

### Example Mail Configuration

#### Mailtrap (Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

#### Gmail (Production)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
```

## Security Configuration

### ALTCHA Spam Protection

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `ALTCHA_HMAC_KEY` | string | - | 64-character HMAC key for ALTCHA |

**Generate HMAC Key:**
```bash
openssl rand -hex 32
```

The generated key should be 64 characters long (32 bytes in hexadecimal).

### IP Blocking

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `BLOCKED_IPS` | string | - | Comma-separated list of blocked IP addresses |

**Note:** This feature was implemented after the project experienced direct attacks in the past. It provides a simple way to block malicious IP addresses at the application level.

**Example:**
```env
BLOCKED_IPS=192.168.1.100,10.0.0.50
```

## Administrator Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `ADMINS` | string | - | Comma-separated list of admin email addresses |
| `LINGUA_ADMIN` | string | - | Primary admin email for language/localization features |

**Example:**
```env
ADMINS=admin@example.com,researcher@example.com
LINGUA_ADMIN=admin@example.com
```

## Application Limits

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `MAX_NUMBER_STUDIES` | integer | 10 | Maximum number of active studies per user |
| `MAX_UPLOAD_SIZE` | integer | 10485760 | Maximum file upload size in bytes (10MB default) |

## Build Configuration

### Vite/Frontend

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `VITE_ENV_MODE` | string | development | Build mode (development/production) |
| `MIX_ENV_MODE` | string | local | Legacy Mix environment mode |

## Logging Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `LOG_CHANNEL` | string | daily | Log channel (single/daily/slack/syslog) |
| `LOG_LEVEL` | string | debug | Minimum log level (debug/info/warning/error) |
| `LOG_SLACK_WEBHOOK_URL` | string | - | Slack webhook for log notifications |

## Broadcasting Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `BROADCAST_DRIVER` | string | log | Broadcast driver (pusher/redis/log/null) |

### Pusher Configuration (if using)
```env
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## Performance Configuration

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `OPCACHE` | boolean | true | Enable PHP OpCache |
| `CACHE_VIEWS` | boolean | false | Cache compiled Blade templates |

## Environment-Specific Configurations

### Development Environment
```env
APP_ENV=local
APP_DEBUG=true
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
MAIL_MAILER=log
```

### Staging Environment
```env
APP_ENV=staging
APP_DEBUG=true
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
MAIL_MAILER=smtp
```

### Production Environment
```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
MAIL_MAILER=smtp
OPCACHE=true
CACHE_VIEWS=true
```

## Testing Configuration

Create a `.env.testing` file for test environment:

```env
APP_ENV=testing
DB_DATABASE=metag_analyze_test
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
FIREBASE_CREDENTIALS=./test-firebase.json
ALTCHA_HMAC_KEY=test_hmac_key_for_testing_only
```

## Validation Checklist

Before deploying, ensure:

1. `APP_KEY` is set (see warning below about key generation)
2. `APP_DEBUG` is `false` in production
3. Database credentials are correct
4. Firebase credentials file exists
5. ALTCHA HMAC key is generated
6. Mail configuration is tested
7. Admin emails are configured
8. Redis is configured if using queues
9. All URLs use HTTPS in production
10. Sensitive values are not committed to version control

**⚠️ CRITICAL WARNING about APP_KEY:**
```bash
# NEVER run this command on a production server with existing data!
php artisan key:generate

# This command generates a new encryption key and will make all
# existing encrypted data unreadable, including:
# - User passwords
# - API tokens
# - Session data
# - Any encrypted database fields
#
# Only use this command:
# - During initial setup
# - On fresh installations
# - In development environments
#
# For production, generate the key locally and add it to .env manually
```

## Troubleshooting

### Common Issues

**"No application encryption key has been specified"**
```bash
php artisan key:generate
```

**"Firebase credentials file not found"**
- Check file path in `FIREBASE_CREDENTIALS`
- Ensure file exists and is readable

**"ALTCHA validation failed"**
- Regenerate HMAC key
- Ensure key is exactly 64 characters

**"Mail not sending"**
- Test with `php artisan tinker`:
```php
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

