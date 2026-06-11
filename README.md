# St. Thomas Evangelical Church of India – UK Parish

Production-ready church website for **steciuk.org**, built with Laravel 13, Filament v4 admin panel, SQLite, Blade, Livewire, and Tailwind CSS v4.

## Features

- **Fully dynamic CMS** — pages, menus, events, news, sermons, ministries, gallery, resources, and church settings editable from admin
- **Rich text editing** — Filament RichEditor (TipTap) for page and content editing
- **Flexible content blocks** — hero, CTA, ministry cards, event/sermon lists, gallery, FAQ, maps, YouTube embeds, and more
- **Mobile-first UI** — hero narrative, bento layouts, sticky dock navigation, gradient titles, PWA install
- **Secure forms** — contact, prayer request, new member, event enquiry, volunteer (honeypot + rate limiting)
- **SEO ready** — dynamic meta tags, Open Graph, JSON-LD, sitemap.xml, robots.txt
- **Role-based admin** — Super Admin, Admin, Editor, and Member roles with custom roles for limited access
- **Migrate-driven reference content** — parish copy, menus, and services ship via `php artisan migrate`

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

On first `migrate`, the app provisions pages, menus, settings, services, and an admin account if they are missing.

### Default admin credentials

Change these immediately before any public deployment.

| Email | Password | Role |
|-------|----------|------|
| admin@steciuk.org | password | Super Admin |

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

After changing paths, run `php artisan storage:link` and ensure folders exist.

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

## Reference content & deploy

Canonical parish copy lives in `app/Support/ReferenceSiteContent.php` and is applied by **migrations** via `ReferenceSiteContentMigrator` (settings, pages, home blocks, worship services, menus). On a fresh database, migrate also provisions core structure (roles, admin, pages).

### Every deploy (local or production)

```bash
git pull
composer install --optimize-autoloader   # production only
npm install && npm run build             # when frontend changed
php artisan site:ensure-paths --link
php artisan migrate --force
php artisan site:doctor
```

Production may also cache config/routes/views after migrate:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run `php artisan site:doctor` any time admin uploads or SQLite paths fail on the server.

When you change reference copy in code, add a migration that calls `ReferenceSiteContentMigrator::apply()`.

### First production server

Same as routine deploy — **`php artisan migrate --force` is sufficient**. Ensure `.env` is configured, `storage/` is writable, and `public/` is the web root.

```bash
git clone git@github.com:maxinjohn/steciuk.git /home/steciuk/steciuk.org
cd /home/steciuk/steciuk.org
cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://steciuk.org
php artisan key:generate
composer install --optimize-autoloader
npm install && npm run build
mkdir -p storage/database && touch storage/database/database.sqlite
chmod -R 775 storage bootstrap/cache
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
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
TRUSTED_PROXIES=*
```

### Cron (recommended on production)

```
* * * * * cd /home/steciuk/steciuk.org && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled SQLite maintenance runs automatically (daily light optimize, weekly analyze, monthly reclaim).

### Production checklist

- [ ] Change default admin password after first sign-in
- [ ] `APP_DEBUG=false` and `EXPOSE_EXCEPTION_DETAILS=false`
- [ ] SQLite file **not** inside `public/`
- [ ] Block `.env` and `storage/` in web server config
- [ ] HTTPS enabled (Let's Encrypt)
- [ ] Cron enabled for `php artisan schedule:run`
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

Do not add `MAIL_*` variables to `.env` — they are ignored once the site is configured. All delivery settings live in the admin panel.

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
├── Database/           ReferenceSiteContentMigrator, menu applicator, provisioner
├── Filament/           Admin panel resources
├── Http/Controllers/   Public site
├── Livewire/Forms/     Secure public forms
├── Models/             Eloquent models
├── Services/           Caching, SEO, mail, SQLite optimiser
└── Support/            ReferenceSiteContent, SeedConfig

config/
├── site.php            Paths and seeding behaviour
└── security.php        Security flags

database/migrations/    Schema + reference content updates
app/Support/ReferenceSiteContent.php   Canonical parish copy
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
