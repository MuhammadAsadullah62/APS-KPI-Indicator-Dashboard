<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site-wide maintenance announcement banner
    |--------------------------------------------------------------------------
    |
    | A dismissible-free notice shown on every page ahead of a planned outage.
    | It renders only while `now()` is before `ends_at`, so it disappears on
    | its own once the window closes — no code change or redeploy needed.
    |
    | Times are read in `timezone` (default: Pakistan local time) and shown to
    | users in that same zone.
    |
    */

    'banner' => [

        'enabled' => (bool) env('MAINTENANCE_BANNER_ENABLED', true),

        'timezone' => env('MAINTENANCE_BANNER_TIMEZONE', 'Asia/Karachi'),

        // Thursday 2:00 PM  ->  Monday 2:00 PM
        'starts_at' => env('MAINTENANCE_BANNER_STARTS_AT', '2026-09-03 14:00'),
        'ends_at' => env('MAINTENANCE_BANNER_ENDS_AT', '2026-09-07 14:00'),
    ],

];
