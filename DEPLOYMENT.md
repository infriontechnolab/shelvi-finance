# Shelvi Finance — Deploy

PHP 8.3+, Composer, MySQL/MariaDB. Doc root = `public/`. No Node on server (assets prebuilt in `public/build/`).
PHP ext: `pdo_mysql mbstring openssl dom ctype json bcmath fileinfo`.

## Do not upload
`.env`, `database/database.sqlite`, `node_modules/`, `.git/`.

## DB
```sql
CREATE DATABASE shelvi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shelvi'@'localhost' IDENTIFIED BY 'STRONG_PW';
GRANT ALL PRIVILEGES ON shelvi.* TO 'shelvi'@'localhost';
FLUSH PRIVILEGES;
```

## Deploy
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# edit .env — DB block (see below)
php artisan migrate --force
php artisan app:setup                 # interactive: roles/perms + login accounts. No demo data.
php artisan config:cache && php artisan route:cache && php artisan view:cache
chmod -R ug+rw storage bootstrap/cache
```

`app:setup` installs roles/permissions and prompts (Laravel Prompts) for the accounts to create —
Administrator (required), Accountant (optional), and the internal owner (optional, hidden in UI, keep
private). Idempotent. Do NOT run bare `db:seed` / `migrate --seed` (loads demo parties/banks + demo logins).

## .env
```dotenv
APP_NAME="Shelvi Finance"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shelvi
DB_USERNAME=shelvi
DB_PASSWORD=STRONG_PW
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
LOG_LEVEL=error

# Required — login is email + emailed OTP only, there is no password fallback.
# Without a working mailer, nobody can log in at all.
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail-address@gmail.com
MAIL_PASSWORD=your-16-char-gmail-app-password
MAIL_FROM_ADDRESS=your-gmail-address@gmail.com
MAIL_FROM_NAME="Shelvi Finance"

# Leave unset in production so each user's OTP goes to their OWN email
# (config/otp.php falls back to the logging-in user's address when this is
# absent). Only set this if you deliberately want every OTP routed to one
# fixed inbox instead.
# OTP_RECIPIENT_EMAIL=
```

Accounts are created interactively by `php artisan app:setup` (above) — no credentials in `.env`.
Gmail App Password: Google Account → Security → 2-Step Verification (must be on) → App passwords.

## Wipe business data (keep users/roles)
```bash
php artisan app:wipe-data          # asks for confirmation
php artisan app:wipe-data --force  # skips it — take a mysqldump backup first
```
Deletes all rows from `banks`, `parties`, `transactions`, `cheques`, `ledger_entries`. Does **not**
touch `users`, `roles`, `permissions`, or role assignments — every existing login keeps working.

## Update later
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan permission:cache-reset
```
Blade/CSS/JS changed → rebuild locally (`npm install && npm run build`), upload `public/build/`.

## Auto-deploy (GitHub Actions)
Every push to `main` triggers `.github/workflows/deploy.yml`: builds assets, rsyncs to the server,
runs `composer install`, `migrate --force`, and rebuilds caches. Requires repo secrets `SSH_HOST`,
`SSH_PORT`, `SSH_USER`, `SSH_PRIVATE_KEY`, `DEPLOY_PATH`. Check progress under the repo's Actions tab.
