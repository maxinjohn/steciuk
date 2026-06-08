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

    /*
    |--------------------------------------------------------------------------
    | Reference data seeding
    |--------------------------------------------------------------------------
    |
    | bootstrap — first install: create all reference records and defaults
    | sync      — deploy updates: upsert seeded records by stable key/slug;
    |             never delete prod-only records; preserve admin passwords
    |             and settings unless overwrite flags are enabled
    | off       — skip seeding (default production)
    |
    */

    'seed' => [
        'mode' => env('SEED_MODE', 'off'),

        'overwrite_settings' => env('SEED_OVERWRITE_SETTINGS', false),

        'overwrite_user_passwords' => env('SEED_OVERWRITE_USER_PASSWORDS', false),

        'overwrite_pages' => env('SEED_OVERWRITE_PAGES', true),
    ],

    'admin_email' => env('ADMIN_EMAIL', 'admin@steciuk.org'),

];
