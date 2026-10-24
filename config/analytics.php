<?php

return [
    'timezone' => env('ANALYTICS_TIMEZONE', 'Asia/Jakarta'),
    'session_timeout_minutes' => 30,
    'retention_days' => 90,
    'visitor_id_days' => 90,
    'sections' => [
        'hero', 'marquee', 'services', 'stats', 'about', 'value_prop',
        'location', 'gallery', 'testimonials', 'contact',
    ],
    'contact_channels' => ['whatsapp', 'instagram', 'email'],
];
