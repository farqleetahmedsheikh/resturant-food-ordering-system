# Mobile API Deployment Notes

This project remains compatible with Namecheap Stellar shared hosting.

## Local Build

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Upload `vendor` if Composer is not available on the server. Do not upload `node_modules`, `.git`, or your local `.env`.

## Server Setup

1. Create a MySQL database in cPanel.
2. Upload Laravel project files.
3. Keep Laravel core files outside `public_html` when possible.
4. Point the domain document root to the Laravel `public` directory.
5. If the document root cannot be changed, move `public` contents into `public_html` and update `index.php` paths carefully.
6. Copy `.env.example` to `.env` on the server and set real credentials.
7. Set `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL=https://your-domain.com`.
8. Set `QUEUE_CONNECTION=sync`, `CACHE_STORE=file`, and `FILESYSTEM_DISK=public`.
9. Set `API_ALLOWED_ORIGINS` to the website/admin/mobile app origin list.
10. Run `php artisan key:generate` if `APP_KEY` is empty.
11. Run migrations and seeders if SSH is available:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Storage Link

Preferred:

```bash
php artisan storage:link
```

If symlinks are blocked by hosting, manually copy or map `storage/app/public` to `public/storage` using cPanel file tools. Uploaded restaurant, category, and menu item images must be reachable under `/storage/...`.

## Mobile API Environment

Recommended production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
CACHE_STORE=file
API_ALLOWED_ORIGINS=https://your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com
SANCTUM_TOKEN_EXPIRATION=43200
```

No Redis, WebSockets, long-running workers, or Supervisor are required.
