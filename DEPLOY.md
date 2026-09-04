# Deployment guide

Live host: **cPanel + MySQL/MariaDB**. Local dev: **PostgreSQL**.
Nothing below touches existing data — every DB change is additive (new tables,
new rows, new indexes). Existing users, observations, sessions, scores, avatars
and department assignments are left exactly as they are.

---

## 0. Merge order into `main`

Three branches, merged in this order. Each is a normal PR / merge.

| # | Branch | What it is | When to deploy |
|---|--------|-----------|----------------|
| 1 | `maintenance-banner` | Site-wide "scheduled maintenance" notice (auto-expires) | **Now** — days before the window, so users are warned |
| 2 | `perf-rbac-overhaul` | Spatie RBAC, query/caching perf, Vite build, avatar service | **During** the Sun 2 PM → Wed 2 PM window |
| 3 | `migration-backfills` | Turns the RBAC user-role backfill into a migration | Right after #2 (or fold it into #2 before merging) |

`maintenance-banner` and `migration-backfills` were branched off
`perf-rbac-overhaul`, so:

- **#1 `maintenance-banner`** has already been rebased onto `main` — it is now a
  single commit (5 files) and merges without pulling in any RBAC/perf code.
  Just open the PR and merge it.
- **#3 `migration-backfills`** merges cleanly after **#2** (the 6 perf commits it
  shares with #2 are recognised as already merged, so only its 1 backfill
  migration lands). If you'd rather do just two deploys, cherry-pick that one
  commit onto `perf-rbac-overhaul` *before* merging #2:
  ```bash
  git checkout perf-rbac-overhaul
  git cherry-pick <backfill-commit-sha>   # "migration: backfill Spatie roles/permissions…"
  git push origin perf-rbac-overhaul
  ```
  Then #2 alone covers everything and you can skip #3.

After each merge to `main`, deploy `main` with the steps in the matching section
below.

---

## 1. Requirements (one-time, on the live server)

**cPanel → Select PHP Version** (or MultiPHP Manager): **PHP 8.2 or 8.3**, with
these extensions ticked:

```
gd  pdo_mysql  mbstring  openssl  tokenizer  xml  ctype  json  bcmath  fileinfo  curl  intl
```

`gd` is the new one — it is required for avatar resizing (`intervention/image`).
If you cannot enable `gd`, the app still works: uploads fall back to storing the
original file untouched.

**Node / npm is NOT needed on the server.** Front-end assets are built locally
and shipped (see below).

**SSH / cPanel Terminal** access is required to run `php artisan …`. If your plan
has no terminal, enable "SSH Access" in cPanel or ask the host.

---

## 2. Live `.env` (edit on the server only — never commit it)

Open the live `.env` in cPanel **File Manager** (show hidden files) or via
terminal. **Do not delete anything.** Make these changes:

```ini
APP_NAME="APSACS Khanewal KPI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-live-domain.tld        # exact https URL, no trailing slash
```

**Database — switch from Postgres to the live MySQL block.** Comment the `pgsql`
lines and un-comment the first MySQL block (the `kpidashb_*` one):

```ini
# DB_CONNECTION=pgsql
# DB_HOST=localhost
# DB_PORT=5432
# DB_DATABASE=postgres
# DB_USERNAME=postgres
# DB_PASSWORD=pakistan@1234

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=kpidashb_kpidashboard
DB_USERNAME=kpidashb_admin
DB_PASSWORD=Hyder"ali123
```

Notes:
- The `"` in the password parses fine unquoted. Only wrap a value in single
  quotes if it ever contains a `#` (dotenv treats `#` as a comment).
- Keep the **existing live `APP_KEY`** — never run `php artisan key:generate` on
  production, it would invalidate every logged-in session.
- Leave `SESSION_DRIVER=database`, `CACHE_STORE=database`,
  `QUEUE_CONNECTION=database` as they are — their tables already exist.
- Optional: set `PULSE_ENABLED=false` unless you run a queue worker / scheduler
  cron (Pulse is admin-only telemetry and is not required for the app).

---

## 3. Build assets locally (before every deploy of `perf-rbac-overhaul`)

`public/build/` is git-ignored, so it will **not** arrive via a git pull. Build
it on your machine — from the merged `main`, so the build picks up the
maintenance-banner styles too — and include it in the upload:

```bash
git checkout main && git pull        # after the banner + perf-rbac merges
npm ci
npm run build                        # writes public/build/manifest.json + assets/*
```

Commit it to the deploy only if you deploy by git — see 4A.

---

## 4. Deploy — pick ONE method

Both methods end with the **same "5. Post-deploy commands"**.

### 4A. Via GitHub (cPanel → Git™ Version Control)

1. **First time:** cPanel → *Git Version Control* → *Create* → clone
   `https://github.com/MuhammadAsadullah62/APS-KPI-Indicator-Dashboard.git`
   into a directory **outside** `public_html`, e.g. `/home/kpidashb/kpi`.
2. Point the domain at the app's `public/`:
   cPanel → *Domains* → your domain → *Document Root* →
   `/home/kpidashb/kpi/public`
   (or, if the host locks the doc root to `public_html`, delete `public_html`
   and `ln -s /home/kpidashb/kpi/public public_html` in Terminal).
3. **Each deploy:**
   ```bash
   cd ~/kpi
   php artisan down --render="errors::503" --retry=120   # brief maintenance page
   git fetch origin
   git checkout main
   git pull --ff-only origin main
   ```
4. **Assets:** because `public/build` is git-ignored, upload your locally-built
   `public/build/` folder now (File Manager → *Upload*, into `~/kpi/public/`),
   **replacing** the old one. Alternatively, on a one-off deploy branch run
   `git add -f public/build && git commit` locally so the pull brings it.
5. Continue at **5. Post-deploy commands**.

> The repo has no `.cpanel.yml`, so cPanel copies files only — it does not run
> Composer/artisan. Do step 5 by hand (or add a `.cpanel.yml` later).

### 4B. Manual upload (cPanel → File Manager)

1. **Locally**, produce a clean release folder:
   ```bash
   git checkout main && git pull
   composer install --no-dev --optimize-autoloader --no-interaction
   npm ci && npm run build
   ```
2. Zip the project **excluding** these (they are dev-only or server-specific):
   `.git/  node_modules/  .env  storage/logs/*  tests/  .github/`
   Keep: `vendor/`, `public/build/`, `bootstrap/`, everything else.
3. cPanel → *File Manager* → go to the app dir (e.g. `~/kpi`) →
   `php artisan down` first (Terminal), then **Upload** the zip → *Extract* →
   confirm overwrite. This replaces code but leaves the live `.env`,
   `storage/app/`, and `public/storage` in place.
4. Fix permissions if the host reset them:
   `storage/` and `bootstrap/cache/` → **755** (recursively), owned by your user.
5. Continue at **5. Post-deploy commands**.

> `vendor/` built on Windows is fine on Linux (all packages here are pure PHP).
> If you'd rather build on the server, skip `composer install` locally and run it
> in step 5 instead (needs Composer in Terminal).

---

## 5. Post-deploy commands (run in cPanel Terminal, in the app dir)

Same for both methods. Order matters.

```bash
# still in maintenance mode from step 4

composer install --no-dev --optimize-autoloader --no-interaction   # skip if vendor/ was uploaded

php artisan migrate --force        # additive only — see "What migrate does" below

# perf-rbac-overhaul ONLY (skip if you merged migration-backfills into it):
php artisan db:seed --class=RolePermissionSeeder --force

php artisan storage:link           # first deploy only; harmless to repeat

php artisan optimize:clear         # drop any stale caches from the old release
php artisan optimize               # re-cache config + routes + views + events
php artisan permission:cache-reset # clear Spatie's permission cache

php artisan up                     # site live again
```

### What `php artisan migrate --force` does on the live DB

| Migration | Effect | Data risk |
|-----------|--------|-----------|
| `…_create_permission_tables` | `CREATE TABLE` roles / permissions / model_has_roles / model_has_permissions / role_has_permissions | none — new tables |
| `…_add_lookup_indexes_for_dashboards` | `CREATE INDEX` on `users(role, wing)` and `media(mediable_type, mediable_id, collection_name)` | none — brief metadata lock only |
| `…_backfill_spatie_roles_…` *(migration-backfills branch)* | inserts the roles/permissions and assigns each existing user the role matching its `users.role` column | none — writes only to the new Spatie tables |

`RolePermissionSeeder` (the `db:seed` line) does the **same backfill** for the
`perf-rbac-overhaul`-only path. It is idempotent — safe to run more than once.

**MySQL note (rare):** if `migrate` stops with
`SQLSTATE[42000] … 1071 Specified key was too long`, your MySQL is older than
5.7.7 / MariaDB 10.2. Fix: edit
`database/migrations/…_add_lookup_indexes_for_dashboards.php` and shorten the
media index to `$table->index(['mediable_id', 'collection_name'], 'media_mediable_collection_index');`
then re-run `php artisan migrate --force`. Nothing else is affected.

---

## 6. Deploying `maintenance-banner` (Phase 1)

Tiny, self-contained change — 1 config file, 1 Blade component, 2 layout lines
(and it removes a dead default test). **No DB, no Composer, no asset build.**
The branch is already rebased onto `main`, so it merges as a single commit.

The current live site loads Tailwind from the CDN, which generates the banner's
amber classes at runtime — so you do **not** need to touch `public/build`.

1. Merge `maintenance-banner` → `main`.
2. On the server:
   ```bash
   cd ~/kpi
   git pull --ff-only origin main       # method 4A
   # — or — upload the 4 changed files via File Manager (method 4B):
   #   config/maintenance.php
   #   resources/views/components/maintenance-banner.blade.php
   #   resources/views/layouts/app.blade.php
   #   resources/views/layouts/guest.blade.php
   php artisan optimize:clear && php artisan optimize
   ```
   No `php artisan down` needed — this change can't break a running page.
3. Adjust the window any time from `.env` (then `php artisan config:clear`):
   ```ini
   MAINTENANCE_BANNER_STARTS_AT="2026-08-30 14:00"
   MAINTENANCE_BANNER_ENDS_AT="2026-09-02 14:00"
   MAINTENANCE_BANNER_TIMEZONE="Asia/Karachi"
   MAINTENANCE_BANNER_ENABLED=true
   ```
   The banner **removes itself** once `ENDS_AT` passes — no redeploy needed.
   Set `MAINTENANCE_BANNER_ENABLED=false` to hide it early.

---

## 7. Rollback

- **Code:** `git checkout <previous-tag-or-sha>` (or re-upload the previous zip),
  then `php artisan optimize:clear && php artisan optimize`.
- **Database:** no rollback needed — the new tables/indexes are inert for the old
  code. If you must: `php artisan migrate:rollback --step=2` for the
  `perf-rbac-overhaul`-only path (permission tables + indexes), or `--step=3` if
  `migration-backfills` was included. Existing data is never touched either way.
- Keep a `mysqldump` from just before the deploy regardless:
  cPanel → *phpMyAdmin* → *Export*, or
  `mysqldump -u kpidashb_admin -p kpidashb_kpidashboard > ~/pre-deploy.sql`

---

## 8. Post-deploy checklist

- [ ] Log in as the principal → dashboard loads, rankings show.
- [ ] `/adminpanel`, `/sechead`, `/teachermanagement`, `/observations` reachable
      for admin/principal; a faculty account gets 403 on them.
- [ ] `/quantitative-observations` redirects leadership to the dashboard.
- [ ] Existing avatars still display; upload a new avatar → it appears and the
      stored file is a small `.webp`.
- [ ] View page source: assets load from `/build/…`, no `cdn.tailwindcss.com`.
- [ ] `/pulse` loads for admin/principal only.
- [ ] `storage/logs/laravel.log` has no new errors.

---

## 9. cPanel housekeeping (optional but recommended)

- **Scheduler cron** (for `pulse:purge` and future scheduled tasks) — cPanel →
  *Cron Jobs* → every minute:
  ```
  * * * * * cd /home/kpidashb/kpi && php artisan schedule:run >> /dev/null 2>&1
  ```
- **Static-asset caching** — add to `public/.htaccess`:
  ```apache
  <IfModule mod_headers.c>
    <FilesMatch "\.(webp|avif|png|jpg|jpeg|gif|svg|woff2|css|js)$">
      Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
  </IfModule>
  ```
- After changing the role → permission matrix in `app/Support/Rbac.php`, re-run
  `php artisan db:seed --class=RolePermissionSeeder --force` and
  `php artisan permission:cache-reset`.
