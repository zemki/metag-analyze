# Deployment

How to get MART Analyze running on an Ubuntu server behind Apache, and how to
update it afterwards.

Three cases, in order of how much already exists:

| | Situation | Start at |
|---|---|---|
| **1** | Blank VM — nothing installed yet | [Case 1](#case-1--blank-ubuntu-server) |
| **2** | VM has Apache, PHP, MySQL; the project is not installed | [Case 2](#case-2--first-installation) |
| **3** | Project is installed; you pulled new code and want it live | [Case 3](#case-3--applying-an-update) |

Throughout, the project lives at `/var/www/html/mart-web` and Apache serves
`/var/www/html/mart-web/public`.

> **A note on how to read this.** Case 2 is the reference: every step says what
> it does and why, so you can judge whether it applies to your situation.
> `php artisan application:install` (see [The short path](#the-short-path))
> collapses the database-and-first-admin steps into one command when you just
> want a working install; it does not replace the rest.

---

## Case 1 — blank Ubuntu server

Targets **Ubuntu 24.04 LTS**, which ships PHP 8.3 — the version
`composer.json` requires (`"php": "8.3.*"`, an exact minor pin). No third-party
PPA is needed.

> On **Ubuntu 22.04** the default is PHP 8.1 and `composer install` will refuse
> to run. Add `ppa:ondrej/php` first:
> `sudo add-apt-repository ppa:ondrej/php && sudo apt update`.

### 1.1 Packages

```bash
sudo apt update
sudo apt install -y \
  apache2 mysql-server git unzip \
  php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-bcmath \
  php8.3-gd php8.3-intl php8.3-zip php8.3-curl php8.3-xml php8.3-opcache
```

Why these extensions: `mysql` (pdo_mysql) for the database; `gd` + `exif` for
QR codes and image uploads; `zip` + `xml` for `maatwebsite/excel` exports;
`intl` + `bcmath` for Filament; `curl` for Firebase/FCM; `mbstring` throughout.

> `php8.3-xml` is easy to miss — the Docker image gets it for free because it is
> compiled in, so it does not appear in `docker/php/Dockerfile`. Without it the
> project export endpoints fail at runtime, not at install time.

### 1.2 Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 1.3 Node 22

Needed **on the server**: the compiled frontend (`public/build`) is not in the
repository, so it has to be built where the code lives.

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node --version   # expect v22.x
```

### 1.4 Apache

```bash
sudo a2enmod proxy_fcgi setenvif rewrite ssl headers
sudo a2enconf php8.3-fpm
sudo systemctl restart apache2
```

Minimal vhost (`/etc/apache2/sites-available/mart-web.conf`) — adjust to match
your TLS setup:

```apache
<VirtualHost *:443>
    ServerName example.uni-bremen.de
    DocumentRoot /var/www/html/mart-web/public

    <Directory /var/www/html/mart-web/public>
        # Required. public/.htaccess holds Laravel's front controller and the
        # HTTP_AUTHORIZATION passthrough that the mobile Bearer-token API
        # depends on. Without AllowOverride All, every mobile API call is
        # unauthenticated and every URL except / returns 404.
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/example.crt
    SSLCertificateKeyFile /etc/ssl/private/example.key

    ErrorLog  ${APACHE_LOG_DIR}/mart-web-error.log
    CustomLog ${APACHE_LOG_DIR}/mart-web-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite mart-web && sudo systemctl reload apache2
```

### 1.5 MySQL

```bash
sudo mysql_secure_installation
```

Then continue with Case 2.

---

## Case 2 — first installation

### 2.1 Get the code

```bash
sudo mkdir -p /var/www/html
cd /var/www/html
sudo git clone https://github.com/zemki/mart-web.git mart-web
cd mart-web
```

**Ownership.** Two workable models:

- *Simple:* everything owned by `www-data`. Deploys then need `sudo -u www-data`.
- *Recommended:* files owned by your deploy user, group `www-data`, with only
  the writable paths (2.8) granted to the group. Apache never needs write
  access to your code.

```bash
sudo chown -R "$USER":www-data /var/www/html/mart-web
```

#### 2.1.1 Install needed PHP stuff

To reflect the needed PHP library stack, we need to either install or update according to `composer.lock` file.

We assume you are in `mart-web` folder. Now either do

```bash
composer install --no-dev --optimize-autoloader
```

or 

```bash
composer update
```

to let the latest dependencies be installed.

#### 2.1.2 Check if artisan works

Try to execute it.

```bash
php artisan
```

### 2.2 Create the database

```bash
sudo mysql
```

Use a password which satisfies rules like more than 8 chars, one uppercase, one lowercase one digit and one special char.

```sql
CREATE DATABASE mart_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mart'@'localhost' IDENTIFIED BY '<CHOOSE_PASSWORD>';
GRANT ALL PRIVILEGES ON mart_testing.* TO 'mart'@'localhost';
FLUSH PRIVILEGES;
```

`utf8mb4` is not optional — questionnaire content and participant answers
contain emoji and non-Latin text.

> ⚠️ **IMPORTANT: If this VM already holds an older MART/metag database:** do not migrate into
> it. Start with a new, empty database.

### 2.3 Configuration

```bash
cp .env.example .env
php artisan key:generate
```

> ⚠️ **IMPORTANT: Back up `APP_KEY` immediately, together with the database.** Every
> participant upload is encrypted with it (`app/Upload.php`). If `APP_KEY` is
> lost or regenerated, existing uploads become permanently undecryptable — the
> files remain on disk but nothing can read them.

Now edit `.env`. These must be changed from the shipped defaults:

| Key | Set to | Why |
|---|---|---|
| `APP_ENV` | `production` | Ships as `local`. |
| `APP_DEBUG` | `false` | Ships as `true` — would expose stack traces and config values. |
| `APP_URL` | `https://example.uni-bremen.de` | Mail links, signed verification URLs, the `/storage` public-disk URL. |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | your values from 2.2 | Ships pointing at `mart_testing` as `root`. |
| `MAIL_MAILER` + `MAIL_HOST` / `MAIL_PORT` / `MAIL_USERNAME` / `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS` | `smtp` and your relay's details | ⚠️ Registration, password-set and verification mails are the **only** way participants get an account. ⛔️ Defaults go nowhere. Verify with `php artisan mail:sendtest you@example.org` before announcing the instance. |
| `ALTCHA_HMAC_KEY` | `openssl rand -hex 32` | Empty breaks the registration captcha. |
| `ADMINS` | comma-separated emails | Drives the Telescope gate and the unverified-user report. Note it does **not** grant admin — see 2.10. |
| `TRUSTED_PROXIES` | proxy IP, or `*` | Required behind a TLS-terminating proxy; otherwise Laravel emits `http://` URLs on an `https://` site and redirects/signed URLs break. |
| `SESSION_SECURE_COOKIE` | `true` | Not in `.env.example`; add it. Keeps the session cookie HTTPS-only. |
| `TELESCOPE_ENABLED` | `false` | Not in `.env.example`, and defaults to **enabled**. See the warning below. |
| `API_DOCS_PROTECT_ACCESS` | `true` | Ships `false`. |
| `API_DOCS_PASSWORD` | a real secret | Ships as `showapi`. |

> ⚠️ **IMPORTANT: Never set `TELESCOPE_ALLOW_ACCESS=true` on a public host.** It does not mean
> "let admins in" — `config/telescope.php` uses it to drop the authorization
> middleware entirely, publishing `/telescope`, and with it every request,
> query and exception, to anyone who visits.

> ⚠️ **IMPORTANT: Why `TELESCOPE_ENABLED=false`.** Telescope defaults to on, records the
> every-minute scheduler run, and nothing prunes `telescope_entries` — the table
> grows without limit. If you do want it, add a `telescope:prune` schedule first.

> Leaving `API_DOCS_PROTECT_ACCESS=false` publishes your entire API surface at
> `/docs/api`, including the mobile endpoints.

### 2.4 PHP dependencies

You might want to run this again.

```bash
composer install --no-dev --optimize-autoloader
```

`--no-dev` omits Pest, Faker and Telescope's dev tooling. `--optimize-autoloader`
builds a classmap — worth real latency under mod_php/FPM.

### 2.5 Frontend build

```bash
npm ci
```

You might want to try to fix most of the issues which arise during ci.

```bash
npm audit fix
```

Then build the files which are deployed.

```bash
npm run build
```

`public/build/` is gitignored, so **without this the site loads with no CSS or
JavaScript at all** — a working but entirely unstyled page. `npm ci` requires
`package-lock.json`, which is committed for exactly this reason; it installs the
locked versions rather than resolving new ones.

### 2.6 Filament assets

```bash
php artisan filament:assets
```

`public/js/` and `public/css/` are gitignored too. Skip this and `/admin`
renders unstyled and non-interactive. Re-run it after any Filament upgrade.

### 2.7 Storage symlink

```bash
php artisan storage:link
```

Creates `public/storage` → `storage/app/public`.

⚠️ **IMPORTANT:** Note that **participant uploads do not live there.** They go to
`storage/app/uploads/`, encrypted, and are served only through a controller —
never directly by Apache. That is deliberate; do not "fix" it by exposing them.

### 2.8 Permissions

Three trees need to be writable by the web server:

```bash
sudo chgrp -R www-data storage bootstrap/cache storage/framework/cache/
sudo chmod -R g+w      storage bootstrap/cache storage/framework/cache/
```

`storage/` covers sessions, the file cache, compiled views, logs and uploads.
If you see a 500 with a `file_put_contents ... Permission denied` in
`storage/logs/`, this step is why.
Also `storage/framework/cache` stores cached data and needs to be writable.

### 2.9 Migrate

```bash
php artisan migrate --force
```

`--force` is required whenever `APP_ENV=production`: without it the command asks
for confirmation and cancels when there is no terminal attached.

### 2.10 Roles and the first administrator

Admin is a **database role**, not a config value. `User::isAdmin()` reads the
`user_roles` table; the `ADMINS` env var is unrelated to it and grants nothing.

> `php artisan make:admin` is misleading: it only appends an address to `ADMINS`
> in `.env`. It does **not** grant admin access, despite what its output says.

Seed the roles **once**:

```bash
php artisan db:seed --force --class='\RolesTableSeeder'
```

Then create your administrator (interactive — needs a real terminal):

```bash
php artisan db:seed --class='\UserRoleTableSeeder'
```

> **Run `RolesTableSeeder` exactly once.** It uses a plain `insert()`, so a
> second run inserts a second full set of roles. Duplicated roles make
> "the admin role" ambiguous and are awkward to unpick later. To check:
> ```bash
> php artisan tinker --execute="echo \App\Role::count();"   # expect exactly 3
> ```

> ### Never run `php artisan db:seed` without `--class` here
>
> ⚠️ **IMPORTANT:** The bare command runs `DatabaseSeeder`, which unconditionally creates two
> accounts with published credentials — `playwright@test.com` / `playwright123`,
> **granted the admin role**, and an API user with the bearer token
> `mart_test_token_2025` valid for five years. On an internet-facing host that
> is a working administrator login for anyone who has read this repository.
>
> Verify after install:
> ```bash
> php artisan tinker --execute="echo \App\User::whereIn('email',['playwright@test.com','mart@mart-web.test'])->count();"
> ```
> Expect `0`.

### 2.11 Caches

Setup the caches which are useful here.

```bash
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

> ⚠️ **IMPORTANT: Do not run `php artisan config:cache`.** Once config is cached Laravel stops
> loading `.env`, and three places in this codebase call `env()` outside
> `config/`: `app/Http/Middleware/BlockIpMiddleware.php` and two lines in
> `app/Notifications/notifyUserforNewCaseWhenAlreadyRegistered.php`. Caching
> config silently disables IP blocking and empties the application name in
> notification emails. Nothing errors — it just quietly misbehaves. If you want
> the speed, move those three reads into `config/` first.

`route:cache` is safe here: no route uses a Closure, and the controller
namespace prefix is set in `RouteServiceProvider`.

### 2.12 The scheduler

**This is what delivers notifications to participants.** Without it the app
appears to work while no participant is ever prompted.

```bash
sudo crontab -u www-data -e
```

```cron
* * * * * cd /var/www/html/mart-web && php artisan schedule:run >> /dev/null 2>&1
```

Confirm it is wired up:

```bash
php artisan schedule:list
```

You should see `notifications:check` every minute and `notification:cleanID`
daily at 01:30.

### 2.13 Queues — nothing to do

`QUEUE_CONNECTION=sync`, so the one queueable notification runs inline. **No
worker is required.** If you ever switch to `database`, note there are no
`jobs`/`failed_jobs` migrations in this repo — you would need to add them.

### 2.14 Verify

```bash
php artisan about        # expect env=production, debug=false, correct APP_URL
```

Then in a browser:

- `https://example.uni-bremen.de/` → login page, **styled**
- Log in as your admin → dashboard loads
- `/admin` → Filament panel, **styled and interactive**
- `/docs/api` → asks for credentials
- `/telescope` → not publicly reachable
- Create a test case with a schedule and confirm a notification arrives

---

## The short path

For a minimum viable install — a migrated database, the base roles, and one
working administrator — steps 2.9 and 2.10 collapse into:

```bash
php artisan application:install
```

It migrates with `--force`, clears caches, creates any missing role **by name**,
then prompts for an administrator email and password (hidden input, never echoed
or printed back). It sets `email_verified_at` so the account can actually get
past the verification gate, and creates the profile row the notification
scheduler needs.

It is safe to re-run: roles are only created when absent, and an existing
account has its password reset rather than being duplicated. It exits `1`
without changing anything if the roles table already contains duplicates.

> ⚠️ **IMPORTANT:** It does **not** replace steps **2.1–2.8** or **2.11–2.13**. You still need the `.env`,
the dependencies, the frontend build, permissions and the cron entry.

---

## Case 3 — applying an update

After pushing changes and pulling them on the server.

```bash
cd /var/www/html/mart-web

php artisan down                              # 1

git pull                                      # 2
composer install --no-dev --optimize-autoloader   # 3
npm ci && npm run build                       # 4
php artisan filament:assets                   # 5

php artisan migrate --force                   # 6

php artisan optimize:clear                    # 7
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

sudo systemctl reload php8.3-fpm              # 8

php artisan up                                # 9
```

Why this order:

1. **Maintenance mode first.** Between the pull and the migration the code and
   schema disagree; serving traffic through that window produces errors.
2. Pull.
3. Only needed if `composer.lock` changed — `git diff HEAD@{1} --name-only composer.lock`
   tells you. Skipping it when nothing changed saves a minute.
4. Always. New or changed frontend code produces new hashed filenames in
   `public/build`; the old manifest points at files that no longer exist.
5. Only strictly needed after a Filament version change, but it is fast and
   harmless.
6. After the new code is in place, so new migrations exist.
7. Clear before re-caching — stale route/view caches survive a pull and will
   serve the old application.
8. **Do not skip.** OPcache holds compiled PHP in memory; without a reload the
   server keeps executing the previous version of your code. This is the single
   most common cause of "I deployed but nothing changed".
9. Back up.

### Before you migrate

```bash
mysqldump -u mart -p mart_web > ~/mart-web-$(date +%F-%H%M).sql
```

Migrations are not reversible in practice here. `migrate:rollback` calls
`down()`, which is `dropIfExists` for every table — it destroys data rather than
reverting a step. A dump is the only real rollback.

---

## Troubleshooting

**The site loads but has no styling at all.**
`npm run build` was not run, or ran as the wrong user and produced an unreadable
`public/build`. Check that `public/build/manifest.json` exists and is readable
by `www-data`.

**`/admin` is unstyled or unresponsive, but the rest of the site is fine.**
`php artisan filament:assets` was not run. `public/js/` and `public/css/` are
gitignored and never arrive via `git pull`.

**Links and redirects use `http://` on an `https://` site; login loops.**
`TRUSTED_PROXIES` is unset. Laravel ignores `X-Forwarded-Proto` from an untrusted
proxy and builds URLs from the scheme it sees, which is plain HTTP behind a
TLS-terminating front end.

**500 error, nothing in the browser.**
`tail -n 50 storage/logs/laravel-$(date +%F).log`. If the log itself is missing
or unwritable, revisit 2.8.

**Deployed new code but the old behaviour persists.**
OPcache. `sudo systemctl reload php8.3-fpm`. If it persists after that, a stale
`route:cache` or `view:cache` — run `php artisan optimize:clear` and re-cache.

**Emails never arrive.**
`MAIL_*` still holds the shipped defaults. Participants cannot register or reset
passwords until this is real. Test the configuration directly:

```bash
php artisan mail:sendtest you@example.org
```

It reports the mailer and transport actually in use, exits non-zero when the
relay refuses the message, and warns explicitly if `MAIL_MAILER=log` — in which
case nothing is sent at all and the message only goes to the log.

**Notifications never fire.**
Check the cron entry exists (`sudo crontab -u www-data -l`), then run
`php artisan schedule:run` by hand and read the output. Also confirm
`php artisan notifications:check` completes on its own.

**IP blocking or the app name in emails stopped working.**
Someone ran `config:cache`. Run `php artisan config:clear`. See 2.11.

---

## What to back up

| | Why |
|---|---|
| The MySQL database | Everything |
| `.env` | Holds `APP_KEY`; without it every upload is unreadable |
| `storage/app/uploads/` | Participant photo/audio/video answers, encrypted |

> ⚠️ **IMPORTANT:** The database and `APP_KEY` must be restored **together** — either alone is not
enough to read uploads.
