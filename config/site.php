<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site data paths (all optional — defaults to Laravel storage/)
    |--------------------------------------------------------------------------
    |
    | Set these in .env to store uploads, SQLite, logs, and cache outside the
    | application directory (e.g. /var/lib/steciuk/storage).
    |
    */

    'paths' => [
        'storage' => env('APP_STORAGE_PATH'),
        'public_uploads' => env('PUBLIC_STORAGE_PATH'),
        'private_uploads' => env('PRIVATE_STORAGE_PATH'),
        'database' => env('DB_DATABASE'),
    ],

    'dir_mode' => env('SITE_DATA_DIR_MODE', '0775'),

    /*
    |--------------------------------------------------------------------------
    | Reference data seeding
    |--------------------------------------------------------------------------
    |
    | Reference parish data is applied automatically after php artisan migrate
    | (sync mode: upsert missing rows, update shipped definitions, preserve prod-only
    | content and custom settings/passwords unless overwrite flags are enabled).
    |
    | bootstrap — force-create all reference records (manual site:bootstrap only)
    | sync      — upsert seeded records; preserve admin passwords and prod settings
    | off       — skip php artisan db:seed only; migrate still syncs reference data
    |
    */

    'seed' => [
        'mode' => env('SEED_MODE', 'off'),

        'overwrite_settings' => env('SEED_OVERWRITE_SETTINGS', false),

        'overwrite_user_passwords' => env('SEED_OVERWRITE_USER_PASSWORDS', false),

        'overwrite_pages' => env('SEED_OVERWRITE_PAGES', true),
    ],

    'admin_email' => env('ADMIN_EMAIL', 'admin@steciuk.org'),

    /*
    |--------------------------------------------------------------------------
    | Admin panel (Filament)
    |--------------------------------------------------------------------------
    |
    | ADMIN_PATH     — URL segment for the control panel (default: admin).
    |                  Example: parish-office → https://yoursite.org/parish-office
    | ADMIN_PANEL_NAME       — Full title in the browser tab and header.
    | ADMIN_PANEL_SHORT_NAME — Short label under the logo in the sidebar.
    |
    */

    'admin' => [
        'path' => (function (): string {
            $raw = strtolower(trim((string) env('ADMIN_PATH', 'admin'), '/'));
            $sanitized = preg_replace('/[^a-z0-9\-]/', '', $raw) ?? '';

            return $sanitized !== '' ? $sanitized : 'admin';
        })(),
        'name' => env('ADMIN_PANEL_NAME', env('APP_NAME', 'STECI UK Parish').' Admin'),
        'short_name' => env('ADMIN_PANEL_SHORT_NAME', 'Parish Admin'),
    ],

];
