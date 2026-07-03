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
```

Accounts are created interactively by `php artisan app:setup` (above) — no credentials in `.env`.

## Update later
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan permission:cache-reset
```
Blade/CSS/JS changed → rebuild locally (`npm install && npm run build`), upload `public/build/`.
