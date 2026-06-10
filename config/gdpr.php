<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Privacy policy version
    |--------------------------------------------------------------------------
    |
    | Increment when the privacy policy materially changes. Stored on consent
    | records so we can demonstrate which version a person accepted.
    |
    */

    'privacy_policy_version' => env('GDPR_PRIVACY_POLICY_VERSION', '2026-06-v2'),

    /*
    |--------------------------------------------------------------------------
    | Data retention (months) — documented in the privacy policy
    |--------------------------------------------------------------------------
    */

    'retention' => [
        'inactive_member_months' => 24,
        'donation_records_years' => 6,
        'form_submission_months' => 24,
    ],

];
