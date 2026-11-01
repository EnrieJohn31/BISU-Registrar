<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client self-registration
    |--------------------------------------------------------------------------
    |
    | Self-registered accounts receive no staff role and cannot act as a
    | client until linked to a verified Student/Alumni ID (Part 04). Turn this
    | off in production until identity proof (D03) and mail (D11) are decided.
    |
    */

    'registration_enabled' => (bool) env('BISU_REGISTRATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Staff account policies (C09, pending office/MIS confirmation under D03)
    |--------------------------------------------------------------------------
    */

    'staff_idle_minutes' => (int) env('BISU_STAFF_IDLE_MINUTES', 30),

    'temporary_password_hours' => (int) env('BISU_TEMPORARY_PASSWORD_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | Development-only two-factor email (C22)
    |--------------------------------------------------------------------------
    |
    | Emails the current authenticator code to the local test inbox for testers
    | without an authenticator app. It only works when APP_ENV=local, so campus
    | servers never send two-factor codes by email. TOTP stays the factor (C09).
    |
    */

    'dev_two_factor_email' => (bool) env('BISU_DEV_TWO_FACTOR_EMAIL', true),

    'dev_mail_inbox_url' => (string) env('BISU_DEV_MAIL_INBOX_URL', 'http://localhost:8026'),

];
