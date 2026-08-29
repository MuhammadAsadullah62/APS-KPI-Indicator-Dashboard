# Deployment

## Requirements

- PHP 8.2+ with the **`gd`** extension (avatar processing via `intervention/image`).
- Node 20+ / npm (asset build).
- PostgreSQL (or MySQL — set `DB_CONNECTION`).

## Production settings

In `.env`:

```
APP_ENV=production
APP_DEBUG=false
```

`CACHE_STORE=database` (default) is fine. The dashboard ranking cache and Spatie's
permission cache both live there.

## Release steps

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build            # compiles resources/css/app.css + app.js into public/build

php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force   # roles + permissions (idempotent)

php artisan optimize               # config + route + view + event cache
php artisan permission:cache-reset # clear Spatie's permission cache
```

### Important

- **`php artisan optimize` (or at least `route:clear`) must run on every deploy.**
  A stale `bootstrap/cache/routes-*.php` silently drops the `can:` middleware
  added to the routes and every page becomes reachable by every role.
- `RolePermissionSeeder` is safe to re-run; it `findOrCreate`s roles/permissions,
  re-syncs each permission set, and re-aligns every user's role with the
  `users.role` column.
- After changing the role→permission matrix in `app/Support/Rbac.php`, re-run the
  seeder and `permission:cache-reset`.

## Storage

Avatars are written to the `public` disk under `avatars/`. Ensure
`php artisan storage:link` has been run and that the web server sends a long
`Cache-Control` for `/storage/*` (files are content-addressed, so
`public, max-age=31536000, immutable` is safe).
