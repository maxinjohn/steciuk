<?php

return [

    'force_https' => env('FORCE_HTTPS', env('APP_ENV') === 'production'),

    'session_lifetime_admin' => (int) env('ADMIN_SESSION_LIFETIME', 120),

    'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),

    'login_decay_minutes' => (int) env('LOGIN_DECAY_MINUTES', 15),

    'block_suspicious_requests' => env('BLOCK_SUSPICIOUS_REQUESTS', true),

    'csp_enabled' => env('CSP_ENABLED', true),

    'require_mfa_for_super_admin' => env('REQUIRE_MFA_SUPER_ADMIN', env('APP_ENV') === 'production'),

    'allow_page_custom_js' => (bool) env('ALLOW_PAGE_CUSTOM_JS', false),

    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'security_contact' => env('SECURITY_CONTACT', 'admin@steciuk.org'),

    /*
    |--------------------------------------------------------------------------
    | Activity log retention
    |--------------------------------------------------------------------------
    |
    | Entries from the last N days cannot be purged from the activity log.
    |
    */
    'audit_log_min_retention_days' => (int) env('AUDIT_LOG_MIN_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Safe error responses
    |--------------------------------------------------------------------------
    |
    | When false (default), public error pages never expose stack traces,
    | environment variables, server details, or request payloads.
    | Set EXPOSE_EXCEPTION_DETAILS=true only on a trusted local machine.
    |
    */
    'expose_exception_details' => (bool) env('EXPOSE_EXCEPTION_DETAILS', false),

    'log_exception_messages' => (bool) env('LOG_EXCEPTION_MESSAGES', true),

];
