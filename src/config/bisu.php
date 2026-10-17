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

];
