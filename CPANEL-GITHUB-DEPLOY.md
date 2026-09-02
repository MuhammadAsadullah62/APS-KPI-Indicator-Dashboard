# Deploy this GitHub project to cPanel

Goal: **move the app out of `public_html`, wire it to GitHub once, then every push
to `main` updates the live site automatically.**

This is the "how do I get from the mess I have now to clean push‑to‑deploy" guide.
For what the artisan/DB steps actually do, see [DEPLOY.md](DEPLOY.md).

---

## Where things are now vs. where they should be

**Now** — the whole Laravel project was uploaded *inside* the web root:

```
/home/kpidashb/
├── public_html/            <- document root (everything below is public!)
│   ├── app/  config/  database/  routes/  vendor/  storage/  ...
│   ├── public/             <- Laravel's real front controller lives here
│   └── APS-KPI-Indicator-Dashboard/   <- stray clone, see step A2
└── www -> public_html
```

Problem: `.env`, `vendor/`, `storage/`, `config/` are all reachable from a browser.

**Target** — app lives *outside* the web root, only `public/` is served:

```
/home/kpidashb/
├── kpi/                    <- the git checkout (NOT web-accessible)
│   ├── app/ config/ routes/ vendor/ storage/ ...
│   └── public/             <- the only folder the web serves
└── public_html -> kpi/public      (symlink)   ... or doc root repointed to kpi/public
```

---

## Part A — one‑time cleanup & setup

Do this once. Set aside ~30–45 min and put the site in maintenance mode first if
it's live.

### A1. Back up the three things git can't give you back

In **File Manager**, from `public_html/`:

1. **`.env`** — select it → *Download*. Keep it safe; you'll put it in `kpi/`.
2. **`storage/`** — select the folder → *Compress* → *zip* → *Download*.
   (Holds logs, cached sessions, and any user uploads under `storage/app/`.)
3. **`public/storage`** — note whether it's a symlink or a real folder with
   uploaded files. If real files, download them too.

Then a **database dump**: cPanel → *phpMyAdmin* → pick `kpidashb_kpidashboard` →
*Export* → *Go*. Save the `.sql`.

### A2. Check the stray `APS-KPI-Indicator-Dashboard/` folder

That folder inside `public_html/` is almost certainly a half‑finished `git clone`.
Open it:

- If it contains a full project copy / a `.git` folder → you don't need it. Note
  anything unique (unlikely), then plan to delete it in step A7.
- If it's empty or tiny → just delete it now.

### A3. Get the code into `/home/kpidashb/kpi` via cPanel Git

cPanel → **Git™ Version Control** → **Create**:

| Field | Value |
|---|---|
| Clone URL | `https://github.com/MuhammadAsadullah62/APS-KPI-Indicator-Dashboard.git` |
| Repository Path | `/home/kpidashb/kpi` |
| Repository Name | `kpi` |

If the repo is private, use a URL with a
[GitHub personal access token](https://github.com/settings/tokens):
`https://<TOKEN>@github.com/MuhammadAsadullah62/APS-KPI-Indicator-Dashboard.git`

After it clones, in *Manage* set the checked‑out branch to **`main`**.

> No SSH/terminal? You can instead make the folder by hand: File Manager → create
> `/home/kpidashb/kpi`, then upload a zip of the repo (built locally, see A5) and
> *Extract* it there. But cPanel Git is what makes future updates one click.

### A4. Put back `.env` and `storage/`

Into the **new** `/home/kpidashb/kpi/` folder:

1. Upload the `.env` you saved in A1.
2. Delete the empty `kpi/storage` that came from git, upload your `storage.zip`,
   *Extract*, so `kpi/storage/` has your real logs/uploads.
3. Set permissions: `kpi/storage/` and `kpi/bootstrap/cache/` → **755**,
   recursive (select folder → *Permissions* → tick *Recurse into subdirectories*).

Confirm `.env` still has the **live MySQL** block and the **existing `APP_KEY`**
(see [DEPLOY.md](DEPLOY.md) section 2). Set `APP_ENV=production`, `APP_DEBUG=false`.

### A5. Get `vendor/` and `public/build/` in place

These are **not** in git. Two ways:

- **Easiest:** let the GitHub Actions workflow (Part B) build and ship them on the
  first deploy. Skip to A6 and run Part B now instead.
- **By hand:** on your PC, from a clean checkout of `main`:
  ```bash
  composer install --no-dev --optimize-autoloader --no-interaction
  npm ci && npm run build
  ```
  Zip `vendor/` and `public/build/`, upload into `kpi/` and `kpi/public/`
  respectively, *Extract*.

### A6. Point the web at `kpi/public`

**Try this first** — cPanel → *Domains* → your domain → *Manage* →
**Document Root** → change to `/home/kpidashb/kpi/public` → *Save*.

**If the doc root is locked** to `public_html`, use a symlink instead. cPanel
Terminal (or *SSH*):
```bash
cd /home/kpidashb
rm -rf public_html                       # you backed it up in A1
ln -s /home/kpidashb/kpi/public public_html
```

**If the host blocks symlinked `public_html`**, last resort: copy the *contents*
of `kpi/public/` into `public_html/`, then edit `public_html/index.php` and change
the two `__DIR__.'/../...'` paths to `'/home/kpidashb/kpi/...'`. (Fragile — avoid
if you can.)

### A7. Delete the old project from `public_html`

Only after A6 works. If you repointed the doc root, empty the old `public_html/`
of the Laravel files (`app/ config/ database/ vendor/ node_modules/ tests/
resources/ routes/ bootstrap/ storage/ public/ scripts/ image/` and the stray
`APS-KPI-Indicator-Dashboard/`). Check `image/` and `scripts/` for uploaded data
first — move anything real into `kpi/storage/app/`.

### A8. First‑run commands

cPanel **Terminal**, in the app dir:
```bash
cd /home/kpidashb/kpi
php artisan storage:link
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force   # RBAC backfill, one time
php artisan optimize:clear
php artisan optimize
php artisan permission:cache-reset
php artisan up
```

### A9. Verify

- Site loads over HTTPS, you can log in, dashboard renders.
- View page source → assets load from `/build/…`.
- Visit `https://your-domain/.env` → must be **404/403**, not a download.
- `kpi/storage/logs/laravel.log` → no new errors.

---

## Part B — updates on every push to `main`

Pick **one**.

### Option 1 — GitHub Actions (recommended: also builds vendor + assets)

The workflow is already in the repo: [.github/workflows/deploy.yml](.github/workflows/deploy.yml).
It builds on GitHub's servers and rsyncs the result to cPanel over SSH.

1. cPanel → **SSH Access** → *Manage SSH Keys* → *Generate a New Key* (no
   passphrase) → **Authorize** the public key → *View/Download* the **private** key.
2. GitHub repo → *Settings* → *Secrets and variables* → *Actions* → add:

   | Secret | Value |
   |---|---|
   | `CPANEL_SSH_HOST` | your server's hostname or IP |
   | `CPANEL_SSH_PORT` | `22` (or whatever *SSH Access* shows) |
   | `CPANEL_SSH_USER` | `kpidashb` |
   | `CPANEL_SSH_KEY` | the full private key text |
   | `CPANEL_APP_PATH` | `/home/kpidashb/kpi` |

3. Merge to `main`. Watch the run in the repo's **Actions** tab.

`.rsync-exclude` in the repo root protects the server's `.env` and `storage/` from
being overwritten or deleted.

### Option 2 — cPanel Git button + `.cpanel.yml`

No secrets, no GitHub Actions, but **you must build `vendor/` + `public/build/`
yourself** (git doesn't carry them) and it's a manual click each time.

1. The repo already has [.cpanel.yml](.cpanel.yml) — open it and fix the three
   paths at the top (`DEPLOYPATH`, `PHP`, `COMPOSER`) for your account.
2. Each deploy: cPanel → *Git Version Control* → *Manage* → **Update from Remote**
   → **Deploy HEAD Commit**. cPanel pulls `main` and runs `.cpanel.yml`.
3. Upload a freshly built `public/build/` (and `vendor/` if you removed the
   `composer install` line) via File Manager after each deploy.

To make Option 2 fire automatically you'd add a GitHub **webhook** to cPanel's
deploy URL — more setup than Option 1 for less benefit. Prefer Option 1.

---

## Rollback

- **Code:** cPanel Git → *Manage* → *Checkout* an earlier commit (Option 2), or
  re‑run the Actions workflow on an earlier commit (Option 1). Then
  `php artisan optimize:clear && php artisan optimize`.
- **DB:** new tables/indexes are inert for old code — usually nothing to do. If
  needed: `php artisan migrate:rollback --step=2`. Restore the A1 dump only in a
  real emergency.

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| `500`, blank page | `kpi/storage/logs/laravel.log`; check `storage/` + `bootstrap/cache/` are **755** and owned by `kpidashb` |
| Browser downloads `index.php` or shows directory listing | doc root / symlink from A6 is wrong |
| `.env` is downloadable | app is still being served from inside `public_html` — finish A6/A7 |
| `git pull` / Actions fails on `vendor` conflicts | never commit `vendor/`; it's git‑ignored — let CI build it |
| assets 404, page unstyled | `public/build/` missing — run `npm run build` and ship it (Option 1 does this) |
| `php: command not found` in `.cpanel.yml` / cron | set `PHP=/usr/local/bin/php` (or `/opt/cpanel/ea-php83/root/usr/bin/php`) |
| migrate: `1071 key too long` | old MySQL — see [DEPLOY.md](DEPLOY.md) section 5 note |
| `Shell access is not enabled on your account` | host disabled SSH shell — ask support to enable jailshell; meanwhile use the appendix below |

---

## Appendix — deploying with NO shell / terminal access

When the host has SSH shell disabled, you can still do everything through
**File Manager** + **Cron Jobs** (cron is not gated by the shell-access flag).

### N1. `.env` — File Manager → `/home/kpidashb/kpi/.env` → *Edit*

Make sure it has:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-real-domain.tld
APP_KEY=base64:...            # MUST be non-empty. Copy it from the old
                              # public_html/.env if this one is blank.
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=kpidashb_kpidashboard
DB_USERNAME=kpidashb_admin
DB_PASSWORD=...
```
Comment out any `pgsql` `DB_*` lines.

### N2. `vendor/` and `public/build/` — File Manager

Open `kpi/vendor/` — it must contain `autoload.php` and folders `laravel/`,
`spatie/`, `intervention/`. Open `kpi/public/build/` — it must contain
`manifest.json` and an `assets/` folder.

If either is missing/incomplete, copy it from your earlier upload: go to
`public_html/`, select `vendor` (or `public/build`), **Copy**, target
`/home/kpidashb/kpi` (or `/home/kpidashb/kpi/public`). Big copy — let it finish.
If you have neither anywhere, the cron in N4 runs `composer install`; assets
(`public/build/`) can only be built on your PC (`npm run build`) and uploaded.

### N3. Permissions — File Manager

Select `kpi/storage` → *Permissions* → `755`, tick **Recurse into
subdirectories** → OK. Repeat for `kpi/bootstrap/cache`.

### N4. Run the setup commands via one Cron Job

cPanel → **Cron Jobs** → *Add New Cron Job*:

- **Common Settings:** "Once Per Minute" is fine (you'll delete it right after), or
  set Minute to ~3 minutes ahead and the rest to `*`.
- **Command:**
  ```
  cd /home/kpidashb/kpi && { echo "=== $(date) ==="; /usr/local/bin/php -v; /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader --no-interaction; /usr/local/bin/php artisan storage:link; /usr/local/bin/php artisan migrate --force; /usr/local/bin/php artisan db:seed --class=RolePermissionSeeder --force; /usr/local/bin/php artisan optimize:clear; /usr/local/bin/php artisan optimize; /usr/local/bin/php artisan permission:cache-reset; /usr/local/bin/php artisan about; } > /home/kpidashb/deploy.log 2>&1
  ```
- Save. Wait for the time to pass (or ~90s for once-per-minute).
- Open **`/home/kpidashb/deploy.log`** in File Manager → *View*. Check each step
  succeeded (look for `Nothing to migrate` or a list of `DONE` migrations, and
  `Configuration cached`, `Routes cached`).
- **Delete the cron job.**

If the log says `composer: command not found`, remove that one segment (your
uploaded `vendor/` covers it). If `php` isn't found at `/usr/local/bin/php`, check
cPanel → *Select PHP Version* for the real path, or try plain `php`.

### N5. Point the web root at `kpi/public`

Try the GUI first: cPanel → **Domains** → your domain → *Manage* →
**Document Root** → `/home/kpidashb/kpi/public` → *Save*.

If that field is read-only, do it with a one-off cron:
1. File Manager: rename `public_html` → `public_html_OLD`.
2. Cron Job (3 min ahead), command:
   ```
   ln -sfn /home/kpidashb/kpi/public /home/kpidashb/public_html
   ```
3. Load the site. If it works, delete the cron and later delete `public_html_OLD`.
4. If the host blocks a symlinked `public_html`, instead: File Manager → copy the
   **contents** of `kpi/public/` into a fresh `public_html/`, then edit
   `public_html/index.php` and change the two `require __DIR__.'/../...'` paths to
   absolute `'/home/kpidashb/kpi/...'` paths.

### N6. Verify — same as A9.

### N7. Ongoing updates without shell (auto-pull cron)

Add a **permanent** Cron Job, every 10 minutes:
```
cd /home/kpidashb/kpi && /usr/bin/git pull --ff-only origin main >> /home/kpidashb/autodeploy.log 2>&1 && /usr/local/bin/php artisan migrate --force >> /home/kpidashb/autodeploy.log 2>&1 && /usr/local/bin/php artisan optimize:clear >> /home/kpidashb/autodeploy.log 2>&1 && /usr/local/bin/php artisan optimize >> /home/kpidashb/autodeploy.log 2>&1 && /usr/local/bin/php artisan permission:cache-reset >> /home/kpidashb/autodeploy.log 2>&1
```
Now a push to `main` goes live within 10 minutes. Caveats:
- **Front-end changes** (JS/CSS) still need `npm run build` on your PC + upload of
  `public/build/` — the server has no Node.
- **New Composer packages** need `composer install` — add it to the cron command,
  or run the N4 cron once after such a change.
- Once the host enables shell access, delete this cron and switch to the cleaner
  **GitHub Actions** flow (Part B, Option 1).
