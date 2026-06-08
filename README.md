# St. Thomas Evangelical Church of India – UK Parish

Production-ready church website for **steciuk.org**, built with Laravel 13, Filament v4 admin panel, SQLite, Blade, Livewire, and Tailwind CSS v4.

## Features

- **Fully dynamic CMS** — pages, menus, events, news, sermons, ministries, gallery, resources, and church settings editable from admin
- **Rich text editing** — Filament RichEditor (TipTap) for page and content editing
- **Flexible content blocks** — hero, CTA, ministry cards, event/sermon lists, gallery, FAQ, maps, YouTube embeds, and more
- **Mobile-first design** — sticky header, slide-out navigation, card layouts, accessible colours
- **Secure forms** — contact, prayer request, new member, event enquiry, volunteer (honeypot + rate limiting)
- **SEO ready** — dynamic meta tags, Open Graph, sitemap.xml, robots.txt, Schema.org Church data
- **Role-based admin** — Super Admin, Editor, Viewer roles with policies

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 20+ and npm
- SQLite extension enabled

## Local Setup

```bash
# Clone and install
composer install
cp .env.example .env
php artisan key:generate

# Create SQLite database outside public directory
mkdir -p storage/database
touch storage/database/database.sqlite

# Update .env — set absolute path for DB_DATABASE:
# DB_DATABASE=/full/path/to/storage/database/database.sqlite

php artisan migrate --seed
php artisan storage:link
npm install && npm run build

# Create additional admin user (optional)
php artisan make:filament-user
```

### Default admin credentials (change immediately in production)

| Email | Password | Role |
|-------|----------|------|
| admin@steciuk.org | password | Super Admin |
| editor@steciuk.org | password | Editor |

- **Public site:** `http://localhost:8000`
- **Admin panel:** `http://localhost:8000/admin`

```bash
php artisan serve
# or use composer dev for server + queue + vite
composer dev
```

## Admin Panel

Navigate to `/admin` and sign in. Key modules:

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

## Deployment (cPanel / Shared VPS)

### 1. Upload files

Upload the project to a directory **above** or **beside** `public_html`, with only the `public/` folder contents mapped to the web root.

Recommended structure:

```
/home/user/steciuk.org/          ← Laravel app root (NOT web accessible)
/home/user/public_html/          ← Symlink or copy of public/
```

### 2. Environment

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://steciuk.org

DB_CONNECTION=sqlite
DB_DATABASE=/home/user/steciuk.org/storage/database/database.sqlite

FILESYSTEM_DISK=public
```

### 3. Post-deploy commands

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build
```

Set directory permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

### 4. Cron (optional, for queues)

```
* * * * * cd /home/user/steciuk.org && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Security checklist

- [ ] Change default admin passwords
- [ ] Set `APP_DEBUG=false`
- [ ] Confirm SQLite file is **not** inside `public/`
- [ ] Block direct access to `.env` and `storage/`
- [ ] Enable HTTPS (Let's Encrypt via cPanel)
- [ ] Configure SMTP for form email notifications

## Backup

Back up these regularly:

```bash
# Database
cp storage/database/database.sqlite backups/database-$(date +%Y%m%d).sqlite

# Uploaded media
tar -czf backups/uploads-$(date +%Y%m%d).tar.gz storage/app/public/
```

## Project Structure

```
app/
├── Filament/          Admin panel resources and pages
├── Http/Controllers/  Public site controllers
├── Livewire/Forms/    Secure public forms
├── Models/            Eloquent models
└── Enums/             Status, roles, block types

resources/views/
├── layouts/           Main site layout
├── pages/             Page templates
├── components/        Reusable UI + content blocks
└── livewire/          Form components

database/seeders/      Sample STECI UK Parish content
```

## Security

Production-hardened with:

- **HTTPS enforcement** (`FORCE_HTTPS=true` in production)
- **Content Security Policy** headers on public pages
- **Suspicious request blocking** (SQL injection / XSS patterns)
- **Secure headers**: HSTS, COOP, CORP, X-Frame-Options, nosniff
- **Rate limiting** on Livewire forms and login attempts
- **Session encryption** and admin session timeout (120 min)
- **2FA (TOTP)** available in admin profile (Filament App Authentication)
- **Security audit log** at `/admin` → Security (Super Admin only)
- **Role-based access**: Super Admin, Editor, Viewer
- **Strong passwords**: 12+ chars, mixed case, numbers, symbols
- **Honeypot + rate limits** on all public forms

Enable MFA: Admin → Profile → Two-factor authentication.

## PWA (Progressive Web App)

The site is installable on mobile and tablet:

- Web manifest at `/manifest.webmanifest`
- Service worker at `/sw.js` (offline fallback + asset caching)
- Install prompt banner on supported browsers
- Apple touch icon and standalone mode

## Page Customization

Every page is fully editable in **Admin → Pages**:

- Rich text content (TipTap editor)
- Hero title, subtitle, style (gradient / image / minimal / immersive)
- Content blocks (15+ section types)
- Custom CSS and JS per page
- Layout variants: standard, bento, minimal, immersive
- Accent colours, SEO fields, template selection

Module listing pages (Events, News, Services, etc.) also pull hero/content from their matching Page record in the database.

## Docker Production Deployment

All ports and startup behaviour are configurable in `.env`:

| Variable | Default | Description |
|----------|---------|-------------|
| `NGINX_HTTP_PORT` | `8080` | Public website port |
| `NGINX_HTTPS_PORT` | `8443` | HTTPS port (when SSL configured) |
| `VITE_DEV_PORT` | `5173` | Vite dev server (dev profile only) |
| `PHP_FPM_PORT` | `9000` | PHP-FPM internal port |
| `RUN_MIGRATIONS` | `true` | Auto-run migrations on startup |
| `RUN_SEED` | `false` | Seed database on first deploy |

### Quick start

```bash
cp .env.example .env
# Edit .env — set APP_KEY (or let entrypoint generate), APP_URL, ports

docker compose build
docker compose up -d

# First deploy with sample content:
RUN_SEED=true docker compose up -d
```

Site: `http://localhost:8080` (or your `NGINX_HTTP_PORT`)  
Admin: `http://localhost:8080/admin`

### Make shortcuts

```bash
make prod      # build + start
make logs      # tail logs
make shell     # app container shell
make fresh     # migrate:fresh --seed
make dev       # dev mode with hot reload
```

### Optional services

```bash
# Queue worker
docker compose --profile queue up -d

# Task scheduler
docker compose --profile scheduler up -d

# Development with Vite HMR
docker compose -f docker-compose.yml -f docker-compose.dev.yml --profile dev up
```

### Architecture

- **app** — PHP 8.3-FPM (Laravel + Filament)
- **nginx** — Nginx 1.27 Alpine (static assets + reverse proxy)
- **queue** — `php artisan queue:work` (optional profile)
- **scheduler** — cron replacement (optional profile)

Persistent volumes: `steci-storage` (SQLite DB + uploads), `steci-bootstrap-cache`

### Production checklist

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://steciuk.org
FORCE_HTTPS=true
RUN_SEED=false
```

Change default admin passwords after first login.

## Mobile & Tablet

The site is built **mobile-first** with:

- 48px minimum touch targets on all buttons and nav items
- 16px form inputs (prevents iOS zoom)
- Safe-area insets for notched phones and PWA standalone mode
- Tablet navigation from 768px (`md` breakpoint)
- Full-width buttons on mobile, inline on tablet+
- Horizontal scroll carousels where needed
- PWA install support with offline fallback
- No horizontal overflow — tested layouts at 320px–1024px

## Tech Stack

- Laravel 13
- Filament v4 (admin)
- Livewire 3
- Tailwind CSS v4
- Spatie Media Library
- Spatie Sitemap
- Mews HTML Purifier

## Licence

Proprietary — St. Thomas Evangelical Church of India UK Parish.
