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
php artisan down                   # maintenance mode — see note below

composer install --no-dev --optimize-autoloader
npm ci && npm run build            # compiles resources/css/app.css + app.js into public/build

php artisan migrate --force        # includes the RBAC backfill (see below)

php artisan optimize               # config + route + view + event cache
php artisan permission:cache-reset # clear Spatie's permission cache
php artisan storage:link           # once, if not already linked

php artisan up
```

### Upgrading an already-live database

`php artisan migrate --force` is now self-sufficient. The
`2026_08_30_120000_backfill_spatie_roles_permissions_and_user_assignments`
migration creates the roles/permissions and assigns every existing user the
Spatie role matching its `users.role` column. It is idempotent and **only
writes to the new Spatie tables** — no existing row (users, observations,
media, departments …) is touched. `db:seed --class=RolePermissionSeeder` is
therefore optional (still fine to run; it does the same thing).

### Important

- **`php artisan optimize` (or at least `route:clear`) must run on every deploy.**
  A stale `bootstrap/cache/routes-*.php` silently drops the `can:` middleware
  added to the routes and every page becomes reachable by every role.
- `RolePermissionSeeder` is safe to re-run; it `findOrCreate`s roles/permissions,
  re-syncs each permission set, and re-aligns every user's role with the
  `users.role` column.
- After changing the role→permission matrix in `app/Support/Rbac.php`, re-run the
  seeder and `permission:cache-reset`.
- **Maintenance mode:** run `php artisan down` before swapping code and
  `php artisan up` after `migrate` completes. Between the file swap and `migrate`
  the new `User` model references the not-yet-created Spatie tables; the model
  guards against it (mid-deploy queries are caught and skipped) but read paths
  such as `@can` in a view would still error, so a short maintenance window is
  the clean way to deploy this release.

## Storage

Avatars are written to the `public` disk under `avatars/`. Ensure
`php artisan storage:link` has been run and that the web server sends a long
`Cache-Control` for `/storage/*` (files are content-addressed, so
`public, max-age=31536000, immutable` is safe).
