# Namecheap Stellar Deployment Guide

This project is designed for shared hosting: Laravel, MySQL, Blade, compiled Vite assets, file sessions/cache, and public file storage. It does not require Redis, WebSockets, Supervisor, queues, or a Node runtime in production.

## Local Build Before Upload

Run these locally before packaging files:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Upload `public/build` after `npm run build`. Do not upload `node_modules`.

## cPanel / Server Setup

1. Create a MySQL database and user in cPanel.
2. Import or migrate the database.
3. Upload the Laravel project files.
4. Copy `.env.example` to `.env` on the server and set:
   - `APP_URL`
   - `APP_KEY`
   - `APP_DEBUG=false`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `FILESYSTEM_DISK=public`
5. If SSH is available, run:

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

For an existing production site, avoid `migrate:fresh` because it deletes data.

## Document Root

Best option: set the domain document root to the Laravel `/public` directory.

If Namecheap/cPanel does not allow changing the document root:

1. Keep Laravel core files outside `public_html`, for example `/home/USER/freshbite`.
2. Copy the contents of Laravel `public/` into `public_html`.
3. Edit `public_html/index.php` paths so they point to the Laravel app:

```php
require __DIR__.'/../freshbite/vendor/autoload.php';
$app = require_once __DIR__.'/../freshbite/bootstrap/app.php';
```

Adjust paths to match the actual server folder names.

## Storage Link

Preferred:

```bash
php artisan storage:link
```

If SSH or symlinks are not available, manually create/copy:

- From: `storage/app/public`
- To: `public/storage`

Uploaded images are stored on the `public` disk and saved as relative paths like `menu-items/file.webp`.

## Permissions

Ensure these are writable by PHP:

```bash
storage/
bootstrap/cache/
```

Typical shared-hosting permissions are `755` for directories and `644` for files, but some servers require writable directories to be `775`.

## What Not To Upload

Do not upload:

- `.git`
- local `.env`
- `node_modules`
- local debug logs if not needed

If Composer is not available on the server, upload the locally built `vendor/` directory. If Composer is available through SSH, run `composer install --no-dev --optimize-autoloader` on the server instead.

## SSL And Caches

Enable SSL in cPanel and set `APP_URL=https://your-domain.com`.

After `.env` changes:

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Production Notes

- Keep `APP_DEBUG=false`.
- Use `QUEUE_CONNECTION=sync`.
- Use `CACHE_STORE=file`.
- Use `SESSION_DRIVER=file`.
- Use `FILESYSTEM_DISK=public`.
- The app uses normal form posts and page refreshes for admin/rider/customer updates.
