# St. Thomas Evangelical Church of India – UK Parish

Production-ready church website for **steciuk.org**, built with Laravel 13, Filament v4 admin panel, SQLite, Blade, Livewire, and Tailwind CSS v4.

## Features

- **Fully dynamic CMS** — pages, menus, events, news, sermons, ministries, gallery, resources, and church settings editable from admin
- **Rich text editing** — Filament RichEditor (TipTap) for page and content editing
- **Flexible content blocks** — hero, CTA, ministry cards, event/sermon lists, gallery, FAQ, maps, YouTube embeds, and more
- **Mobile-first UI** — hero narrative, bento layouts, sticky dock navigation, gradient titles, PWA install
- **Secure forms** — contact, prayer request, new member, event enquiry, volunteer (honeypot + rate limiting)
- **SEO ready** — dynamic meta tags, Open Graph, JSON-LD, sitemap.xml, robots.txt
- **Role-based admin** — Super Admin, Editor, Viewer roles with policies
- **Env-configurable** — data paths, seeding behaviour, and security flags via `.env`

## Requirements

- PHP 8.3 or 8.4 with extensions: `sqlite3`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd` or `imagick`
- Composer 2
- Node.js 20+ and npm
- Web server (Apache or nginx) pointing document root at `public/`

## Quick start (local)

```bash
git clone git@github.com:maxinjohn/steciuk.git
cd steciuk
composer install
cp .env.example .env
php artisan key:generate

mkdir -p storage/database
touch storage/database/database.sqlite

php artisan migrate
php artisan site:bootstrap --force
php artisan storage:link
npm install && npm run build

php artisan serve
```

| URL | Address |
|-----|---------|
| Public site | http://localhost:8000 |
| Admin panel | http://localhost:8000/admin |

Or run server, queue, logs, and Vite together:

```bash
composer dev
```

### Default admin credentials

Change these immediately before any public deployment.

| Email | Password | Role |
|-------|----------|------|
| admin@steciuk.org | password | Super Admin |
| editor@steciuk.org | password | Editor |

## Environment configuration

Copy `.env.example` to `.env` and generate a key:

```bash
cp .env.example .env
php artisan key:generate
```

### Key variables

| Variable | Local default | Production |
|----------|---------------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost:8000` | `https://steciuk.org` |
| `DB_DATABASE` | `storage/database/database.sqlite` | absolute path outside `public/` |
| `FILESYSTEM_DISK` | `public` | `public` |
| `CACHE_STORE` | `file` | `file` |
| `FORCE_HTTPS` | `false` | `true` |
| `SEED_MODE` | `bootstrap` | `off` |
| `EXPOSE_EXCEPTION_DETAILS` | `false` | `false` |

### Site data paths (optional)

Store logs, cache, uploads, and the database outside the application directory:

```env
APP_STORAGE_PATH=/var/lib/steciuk/storage
PUBLIC_STORAGE_PATH=/var/lib/steciuk/pub_uploads
PRIVATE_STORAGE_PATH=/var/lib/steciuk/private_uploads
DB_DATABASE=/var/lib/steciuk/database/database.sqlite
PUBLIC_STORAGE_URL=/storage
```

| Variable | What it is |
|----------|------------|
| `APP_STORAGE_PATH` | Laravel storage (logs, cache, framework) |
| `PUBLIC_STORAGE_PATH` | Upload folder on disk |
| `PRIVATE_STORAGE_PATH` | Private/Filament media folder |
| `DB_DATABASE` | SQLite file path |
| `PUBLIC_STORAGE_URL` | Browser URL path only (`/storage`) — **not** a folder path |

Relative paths like `../site_data/storage` resolve from the project root. After changing paths, run `php artisan storage:link` and ensure folders exist.

### Security variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `FORCE_HTTPS` | `false` | Redirect HTTP → HTTPS |
| `CSP_ENABLED` | `true` | Content Security Policy headers |
| `BLOCK_SUSPICIOUS_REQUESTS` | `true` | Block common injection patterns |
| `REQUIRE_MFA_SUPER_ADMIN` | `false` | Require 2FA for super admins |
| `MAX_LOGIN_ATTEMPTS` | `5` | Admin login rate limit |
| `LOGIN_DECAY_MINUTES` | `15` | Lockout window |
| `TRUSTED_PROXIES` | *(empty)* | Set to `*` behind nginx/load balancer |
| `EXPOSE_EXCEPTION_DETAILS` | `false` | Never enable on production |

## Reference data & seeding

All parish content (pages, menus, events, news, services, etc.) lives in `database/seeders/` as **idempotent reference data**.

| `SEED_MODE` | Behaviour |
|-------------|-----------|
| `bootstrap` | First install — creates all reference pages, menus, settings, sample content |
| `sync` | Deploy update — upserts seeded records by slug/key; **never deletes prod-only data** |
| `off` | No seeding (default production after bootstrap) |

### Commands

```bash
# First install only (local or production)
php artisan site:bootstrap --force

# Optional: push dev reference content to production without wiping prod-only records
php artisan site:sync-reference-data --force
```

**Important:** Run `site:bootstrap` once on a new server. On later deploys, run **migrations only** — do not re-bootstrap unless you intentionally want to reset reference content.

### What sync preserves

- News, events, pages created only in production (no matching slug)
- Custom menu links added in admin (no `seed_key`)
- Admin passwords (unless `SEED_OVERWRITE_USER_PASSWORDS=true`)
- Settings edited in production (unless `SEED_OVERWRITE_SETTINGS=true`)

## Production hosting (PHP / cPanel / VPS)

Place the Laravel app **outside** the web root. Only `public/` should be web-accessible.

```
/home/steciuk/steciuk.org/     ← app root (NOT web accessible)
/home/steciuk/public_html/     ← document root → symlink or copy of public/
```

### First deploy

```bash
git clone git@github.com:maxinjohn/steciuk.git /home/steciuk/steciuk.org
cd /home/steciuk/steciuk.org

cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://steciuk.org, etc.
php artisan key:generate

composer install --optimize-autoloader
npm install
npm run build

mkdir -p storage/database
touch storage/database/database.sqlite
chmod -R 775 storage bootstrap/cache

php artisan migrate --force
php artisan site:bootstrap --force
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R steciuk:steciuk /home/steciuk/steciuk.org
```

Set in `.env` after bootstrap:

```env
SEED_MODE=off
```

### Routine deploy (after git pull / merge to main)

Run this on the server whenever you release new code. **No seeding** — migrations only.

```bash
cd /home/steciuk/steciuk.org
git pull origin main

composer install --optimize-autoloader

npm install
npm run build

chmod -R 775 storage bootstrap/cache

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R steciuk:steciuk /home/steciuk/steciuk.org
```

Optional after deploy:

```bash
php artisan site:ensure-roles
php artisan site:ensure-admin
php artisan site:sync-reference-data --force   # only when pushing dev content changes
```

### Production `.env` example

```env
APP_ENV=production
APP_DEBUG=false
EXPOSE_EXCEPTION_DETAILS=false
APP_URL=https://steciuk.org

DB_CONNECTION=sqlite
DB_DATABASE=/home/steciuk/steciuk.org/storage/database/database.sqlite

FILESYSTEM_DISK=public
CACHE_STORE=file

FORCE_HTTPS=true
SEED_MODE=off
TRUSTED_PROXIES=*
```

### Cron (recommended on production)

```
* * * * * cd /home/steciuk/steciuk.org && php artisan schedule:run >> /dev/null 2>&1
```

This runs scheduled SQLite maintenance automatically:

| Schedule | Command | Purpose |
|----------|---------|---------|
| Daily 03:15 | `db:optimize-sqlite --light` | WAL checkpoint + incremental vacuum |
| Weekly Sun 04:00 | `db:optimize-sqlite` | ANALYZE + query planner optimize |
| Monthly 1st 04:30 | `db:optimize-sqlite --reclaim` | Full VACUUM + incremental auto-vacuum |

Run once manually after deploy: `php artisan db:optimize-sqlite --reclaim`

### Production checklist

- [ ] Change default admin passwords
- [ ] `APP_DEBUG=false` and `EXPOSE_EXCEPTION_DETAILS=false`
- [ ] SQLite file **not** inside `public/`
- [ ] Block `.env` and `storage/` in web server config
- [ ] HTTPS enabled (Let's Encrypt)
- [ ] Cron enabled for `php artisan schedule:run` (SQLite maintenance)
- [ ] `php artisan db:optimize-sqlite --reclaim` once after first deploy
- [ ] `SEED_MODE=off` after first bootstrap
- [ ] Enable MFA for super admin (`REQUIRE_MFA_SUPER_ADMIN=true`)

## Admin panel

Navigate to `/admin` and sign in.

| Module | Purpose |
|--------|---------|
| Pages | Custom pages with rich text and content blocks |
| Menu Items | Header, footer, and mobile navigation |
| Worship Services | UK service locations (Manchester, Leicester, etc.) |
| Events / News / Sermons | Published content with SEO fields |
| Ministries | Ministry pages with descriptions and contact |
| Gallery | Albums and photos |
| Resources | Liturgy, lectionary, forms, safeguarding docs |
| Form Submissions | View contact and prayer form entries |
| Church Settings | Logo, contact info, social links, SEO defaults |
| Email Setup | SMTP or PHP sendmail for contact forms |
| Users | Manage admin accounts (Super Admin only) |

## Mail

Contact forms and admin notifications are configured in **Admin → Site Settings → Email Setup**.

1. Choose **PHP sendmail** for typical shared hosting, or **SMTP** for external mail servers.
2. Set the from address and save.
3. Use **Send test email** to verify delivery.

Do not add `MAIL_*` variables to `.env` — they are ignored once the site is bootstrapped. All delivery settings live in the admin panel.

## Backup

```bash
cp storage/database/database.sqlite backups/database-$(date +%Y%m%d).sqlite
tar -czf backups/uploads-$(date +%Y%m%d).tar.gz storage/app/public/
```

## Security

- HTTPS enforcement (`FORCE_HTTPS=true`)
- Content Security Policy on public pages
- Suspicious request blocking (SQL injection / XSS patterns)
- Secure headers: HSTS, COOP, CORP, X-Frame-Options, nosniff
- Rate limiting on forms and admin login
- Session encryption and admin session timeout (120 min)
- 2FA (TOTP) in admin profile
- Security audit log (Super Admin only)
- Safe error pages — no stack traces or env leakage
- Honeypot + rate limits on all public forms

Enable MFA: Admin → Profile → Two-factor authentication.

## PWA

- Web manifest at `/manifest.webmanifest`
- Service worker at `/sw.js` (static assets only; HTML not cached)
- Install prompt on supported browsers
- Offline fallback page

## Project structure

```
app/
├── Console/Commands/   site:bootstrap, site:sync-reference-data, site:ensure-admin
├── Filament/           Admin panel resources
├── Http/Controllers/   Public site
├── Livewire/Forms/     Secure public forms
├── Models/             Eloquent models
├── Services/           Caching, SEO, mail, SQLite optimiser
└── Support/            SeedConfig, embed sanitiser

config/
├── site.php            Paths and seeding behaviour
└── security.php        Security flags

database/seeders/       Idempotent reference parish content
```

## Tech stack

- Laravel 13
- Filament v4
- Livewire 3
- Tailwind CSS v4
- PHP 8.3 / 8.4
- Spatie Media Library & Sitemap
- Mews HTML Purifier

## Licence

Proprietary — St. Thomas Evangelical Church of India UK Parish.
