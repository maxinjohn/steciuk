# St. Thomas Evangelical Church of India – UK Parish

Production-ready church website for **steciuk.org**, built with Laravel 13, Filament v4 admin panel, SQLite, Blade, Livewire, and Tailwind CSS v4.

## Features

- **Fully dynamic CMS** — pages, menus, events, news, sermons, ministries, gallery, resources, and church settings editable from admin
- **Rich text editing** — Filament RichEditor (TipTap) for page and content editing
- **Flexible content blocks** — hero, CTA, ministry cards, event/sermon lists, gallery, FAQ, maps, YouTube embeds, and more
- **Mobile-first Gen Z UI** — bento layouts, feed cards, sticky dock navigation, gradient titles, PWA install
- **Secure forms** — contact, prayer request, new member, event enquiry, volunteer (honeypot + rate limiting)
- **SEO ready** — dynamic meta tags, Open Graph, JSON-LD, sitemap.xml, robots.txt
- **Role-based admin** — Super Admin, Editor, Viewer roles with policies
- **Env-configurable** — data paths, ports, seeding behaviour, and security flags via `.env`

## Requirements

- PHP 8.3 or 8.4
- Composer 2
- Node.js 20+ and npm
- SQLite extension enabled

## Quick start (local, no Docker)

```bash
git clone git@github.com:maxinjohn/steciuk.git
cd steciuk
composer install
cp .env.example .env
php artisan key:generate

mkdir -p storage/database
touch storage/database/database.sqlite

php artisan migrate
php artisan site:bootstrap
php artisan storage:link
npm ci && npm run build

php artisan serve
```

| URL | Address |
|-----|---------|
| Public site | http://localhost:8000 |
| Admin panel | http://localhost:8000/admin |

Or run everything together (server, queue, logs, Vite):

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

Copy `.env.example` to `.env` and adjust. The example file contains every variable with standard defaults and inline comments.

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
| `MAIL_MAILER` | `log` | `smtp` |
| `FORCE_HTTPS` | `false` | `true` |
| `SEED_MODE` | `bootstrap` | `off` |
| `EXPOSE_EXCEPTION_DETAILS` | `false` | `false` |

### Site data paths (optional)

Store uploads and the database outside the application directory:

```env
APP_STORAGE_PATH=/var/lib/steciuk/storage
PUBLIC_STORAGE_PATH=/var/lib/steciuk/uploads
PRIVATE_STORAGE_PATH=/var/lib/steciuk/private
DB_DATABASE=/var/lib/steciuk/storage/database/database.sqlite
```

Docker bind mount (instead of named volumes):

```env
STORAGE_HOST_PATH=/var/lib/steciuk/storage
BOOTSTRAP_CACHE_HOST_PATH=/var/lib/steciuk/bootstrap-cache
```

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

### Docker ports

| Variable | Default | Description |
|----------|---------|-------------|
| `NGINX_HTTP_PORT` | `8080` | Public website |
| `NGINX_HTTPS_PORT` | `8443` | HTTPS (when SSL configured) |
| `VITE_DEV_PORT` | `5173` | Vite dev server (dev profile) |
| `PHP_FPM_PORT` | `9000` | PHP-FPM internal |
| `RUN_MIGRATIONS` | `true` | Auto-migrate on container start |
| `RUN_SEED` | `false` | Auto-seed on container start |

## Reference data & seeding

All parish content (pages, menus, events, news, services, etc.) lives in `database/seeders/` as **idempotent reference data**.

| `SEED_MODE` | Behaviour |
|-------------|-----------|
| `bootstrap` | First install — creates all reference pages, menus, settings, sample content |
| `sync` | Deploy update — upserts seeded records by slug/key; **never deletes prod-only data** |
| `off` | No seeding (default production after bootstrap) |

### Commands

```bash
# First install (local or production)
php artisan site:bootstrap --force

# Push dev content changes to production without wiping prod-only records
php artisan site:sync-reference-data --force
```

### What sync preserves

- News, events, pages created only in production (no matching slug)
- Custom menu links added in admin (no `seed_key`)
- Admin passwords (unless `SEED_OVERWRITE_USER_PASSWORDS=true`)
- Settings edited in production (unless `SEED_OVERWRITE_SETTINGS=true`)

### What sync updates

- Seeded pages, events, news, ministries, services, etc. (matched by **slug**)
- Menu structure for seeded items (matched by **seed_key**)
- Home page content blocks (matched by **seed_key**)

### Production first deploy

```env
SEED_MODE=off
RUN_SEED=false
```

Then run once:

```bash
php artisan site:bootstrap --force
```

Or for Docker first boot only: `RUN_SEED=true` and `SEED_MODE=bootstrap` in `.env`, then set back to `off`.

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
| Users | Manage admin accounts (Super Admin only) |

## Docker deployment

```bash
cp .env.example .env
# Edit .env — APP_URL, ports, optional STORAGE_HOST_PATH

docker compose build
docker compose up -d

# First deploy with reference content:
docker compose exec app php artisan site:bootstrap --force
```

| URL | Address |
|-----|---------|
| Site | http://localhost:8080 (or `NGINX_HTTP_PORT`) |
| Admin | http://localhost:8080/admin |

### Make shortcuts

```bash
make prod       # build + start
make bootstrap  # first-time reference data
make sync       # sync dev changes to prod safely
make logs       # tail logs
make shell      # app container shell
make dev        # dev mode with hot reload
```

### Optional services

```bash
docker compose --profile queue up -d      # queue worker
docker compose --profile scheduler up -d  # task scheduler
docker compose -f docker-compose.yml -f docker-compose.dev.yml --profile dev up  # Vite HMR
```

### Architecture

- **app** — PHP 8.4-FPM (Laravel + Filament)
- **nginx** — Nginx 1.27 Alpine (static assets + reverse proxy)
- **queue** — `php artisan queue:work` (optional profile)
- **scheduler** — cron replacement (optional profile)

Persistent data: `steci-storage` volume (or `STORAGE_HOST_PATH` bind mount) for SQLite + uploads.

## Deployment (cPanel / shared VPS)

### 1. Upload files

Place the Laravel app **outside** the web root. Only `public/` should be web-accessible.

```
/home/user/steciuk.org/     ← app root (NOT web accessible)
/home/user/public_html/     ← contents of public/ or symlink
```

### 2. Environment

```env
APP_ENV=production
APP_DEBUG=false
EXPOSE_EXCEPTION_DETAILS=false
APP_URL=https://steciuk.org

DB_CONNECTION=sqlite
DB_DATABASE=/home/user/steciuk.org/storage/database/database.sqlite

FILESYSTEM_DISK=public
CACHE_STORE=file
MAIL_MAILER=smtp
FORCE_HTTPS=true
SEED_MODE=off
TRUSTED_PROXIES=*
```

### 3. Post-deploy commands

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan site:bootstrap --force   # first deploy only
php artisan storage:link
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

### 4. Cron (optional)

```
* * * * * cd /home/user/steciuk.org && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Production checklist

- [ ] Change default admin passwords
- [ ] `APP_DEBUG=false` and `EXPOSE_EXCEPTION_DETAILS=false`
- [ ] SQLite file **not** inside `public/`
- [ ] Block `.env` and `storage/` in web server config
- [ ] HTTPS enabled (Let's Encrypt)
- [ ] SMTP configured for form notifications
- [ ] `SEED_MODE=off` after bootstrap
- [ ] Enable MFA for super admin (`REQUIRE_MFA_SUPER_ADMIN=true`)

## Backup

```bash
# Database
cp storage/database/database.sqlite backups/database-$(date +%Y%m%d).sqlite

# Uploads
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
├── Console/Commands/   site:bootstrap, site:sync-reference-data
├── Filament/           Admin panel resources
├── Http/Controllers/   Public site
├── Livewire/Forms/     Secure public forms
├── Models/             Eloquent models
├── Services/           Caching, SEO, SQLite optimiser
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
