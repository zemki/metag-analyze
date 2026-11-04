# Deployment

How to run Metag Analyze on an Ubuntu server behind Apache, and how to update it.
For a local development setup, see the [Installation Guide](./INSTALLATION.md).

| Situation | Start at |
|---|---|
| Blank server | [Server setup](#server-setup) |
| Server is ready, the app is not installed | [First installation](#first-installation) |
| App is installed, you want new code live | [Updating](#updating) |

The app lives at `/var/www/html/metag-analyze` and Apache serves its `public/` folder.

## Server setup

The code requires PHP 8.3 (`"php": "8.3.*"` in `composer.json`), which is the default on
Ubuntu 24.04. CI builds with Node 22.

```bash
sudo apt update
sudo apt install -y apache2 mysql-server git unzip \
  php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-bcmath php8.3-gd \
  php8.3-intl php8.3-zip php8.3-curl php8.3-xml php8.3-opcache

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

sudo mysql_secure_installation
sudo a2enmod proxy_fcgi setenvif rewrite ssl headers
sudo a2enconf php8.3-fpm
sudo systemctl restart apache2
```

Minimal virtual host, to adapt to your TLS setup:

```apache
<VirtualHost *:443>
    ServerName example.uni-bremen.de
    DocumentRoot /var/www/html/metag-analyze/public

    <Directory /var/www/html/metag-analyze/public>
        # Required: public/.htaccess routes every URL through Laravel and passes
        # the Authorization header on. Without it, all Bearer API calls fail.
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/example.crt
    SSLCertificateKeyFile /etc/ssl/private/example.key
</VirtualHost>
```

> **`APP_ENV=production` assumes the `/metag` subfolder.** `AppServiceProvider` then serves
> Livewire and Telescope under `/metag/...`, and the graph breadcrumb links there too. A
> server at the domain root needs those paths changed, or it has to run another `APP_ENV`.

## First installation

### 1. Code and dependencies

```bash
cd /var/www/html
sudo git clone https://github.com/zemki/metag-analyze.git
sudo chown -R "$USER":www-data metag-analyze
cd metag-analyze

composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan filament:assets
```

`public/build`, `public/js` and `public/css` are gitignored, so these steps have to run on
the server. Without them, the site or `/admin` loads unstyled.

### 2. Two databases

Main data and MART data live in separate databases. The `mart` connection uses the same
host, user and password as the main one, so one user needs access to both.

```sql
CREATE DATABASE metag CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE metag_mart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'metag'@'localhost' IDENTIFIED BY '<CHOOSE_PASSWORD>';
GRANT ALL PRIVILEGES ON metag.* TO 'metag'@'localhost';
GRANT ALL PRIVILEGES ON metag_mart.* TO 'metag'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configuration

```bash
cp .env.example .env
php artisan key:generate
```

**Back up `APP_KEY` right away.** Uploaded files are encrypted with it. If it's lost or
regenerated, they can't be read again.

Set these in `.env`:

| Key | Value | Notes |
|---|---|---|
| `APP_ENV` | `production` | See the `/metag` note above |
| `APP_DEBUG` | `false` | `true` exposes stack traces |
| `APP_URL` | your URL | Used in mail links and signed URLs |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | from step 2 | |
| `MART_DB_DATABASE` | `metag_mart` | |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | your relay | Participants get their account by mail. Test with `php artisan mail:sendtest you@example.org`. |
| `ALTCHA_HMAC_KEY` | `openssl rand -hex 32` | Registration captcha |
| `ADMINS` | comma-separated emails | Telescope access and the unverified-users report. Does not grant the admin role. |
| `FIREBASE_CREDENTIALS` | path below `storage/app/` | Push notifications |
| `TRUSTED_PROXIES` | proxy IPs, or `*` | Needed behind a TLS-terminating proxy, or Laravel builds `http://` URLs |
| `SESSION_SECURE_COOKIE` | `true` | Not in `.env.example`, add it |
| `API_DOCS_PROTECT_ACCESS` | `true` | Keep this on. `false` opens `/docs/api` to everyone. |
| `API_DOCS_PASSWORD` | a real secret | When empty, `/docs/api` returns 503 |
| `TELESCOPE_ENABLED` | `false` | Not in `.env.example`, and it defaults to on |

### 4. Folders

```bash
php artisan storage:link
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R g+w storage bootstrap/cache
```

### 5. Database, roles and first administrator

```bash
php artisan metag:install
```

This migrates both databases, creates the `admin`, `researcher` and `user` roles if they're
missing, and asks for an administrator's email and password. It's safe to run again; for an
existing account, it resets the password.

**Don't run a plain `php artisan db:seed` on a server.** It's meant for demo data, and it can
add test accounts with published credentials. The Playwright admin is created in `local` and
`testing`, and the MART test token everywhere except `production`, so a server on
`APP_ENV=local` gets both.

### 6. Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

With config cached, `.env` isn't read any more. Run `php artisan config:cache` again after
changing it, and never call `env()` outside `config/`.

### 7. Scheduler

This is what sends participant notifications. Without it, nobody gets reminded and nothing
looks wrong.

```bash
sudo crontab -u www-data -e
```

```cron
* * * * * cd /var/www/html/metag-analyze && php artisan schedule:run >> /dev/null 2>&1
```

`php artisan schedule:list` should show:

| Command | When |
|---|---|
| `notifications:check` | Every minute |
| `notification:cleanID` | Daily at 01:30, removes push tokens of ended cases |
| `telescope:prune --hours=168` | Daily at 03:17, keeps 7 days |

No queue worker is needed: `QUEUE_CONNECTION=sync` runs queued notifications inline, and
this repo has no `jobs` table migration.

### 8. Check it

```bash
php artisan about
```

Then in a browser:

- `/` shows a styled login page, and your administrator can sign in.
- `/admin` is styled and interactive.
- `/docs/api` asks for credentials.
- `/telescope` isn't publicly reachable.
- A test notification reaches a device.

## Managing roles

Roles live in the database. `ADMINS` in `.env` doesn't grant any of them.

| Role | Access |
|---|---|
| `admin` | Dashboard and the `/admin` panel |
| `researcher` | Dashboard |
| `user` | Mobile app only |

```bash
php artisan roleadd:admin user@example.org
php artisan roleremove:admin user@example.org
```

The same pair exists for `researcher` and `user`. Adding a role the account already has
changes nothing. Removing the last role asks first; `--force` skips that.

Duplicate rows in the `roles` table can be merged with `php artisan role:cleanup --dry-run`,
then `php artisan role:cleanup`.

## Updating

Back up both databases first. A dump is the only safe way back from a migration.

```bash
cd /var/www/html/metag-analyze
mysqldump -u metag -p --databases metag metag_mart > ~/metag-$(date +%F-%H%M).sql

php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan filament:assets

php artisan migrate --force

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

sudo systemctl reload php8.3-fpm
php artisan up
```

One `migrate` covers both databases. Reloading PHP matters: without it, OPcache keeps running
the old code.

## Backups

| What | Why |
|---|---|
| Both databases | All project, case and MART data |
| `.env` | Holds `APP_KEY` |
| `storage/app/` | Encrypted uploads: MART files in `mart/`, MeTag files in `project{id}/` |

Restore the databases and `APP_KEY` together, or the uploads can't be read.

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| Site loads without styling | `npm run build` didn't run, or `public/build` isn't readable by `www-data` |
| `/admin` is unstyled | `php artisan filament:assets` didn't run |
| Every API call returns 401 | `AllowOverride All` is missing, so the Authorization header is dropped |
| `http://` links, login loops | `TRUSTED_PROXIES` isn't set |
| 500 with `Permission denied` in the log | Revisit [Folders](#4-folders) |
| New code doesn't take effect | Reload PHP, then `php artisan optimize:clear` and re-cache |
| A `.env` change has no effect | Run `php artisan config:cache` |
| No mail arrives | Run `php artisan mail:sendtest you@example.org` |
| No notifications | Check the cron entry, then run `php artisan notifications:check` by hand |
