<?php

return [
    'timezone' => env('ANALYTICS_TIMEZONE', 'Asia/Jakarta'),
    'session_timeout_minutes' => 30,
    'retention_days' => 90,
    'visitor_id_days' => 90,
    'sections' => [
        'intro', 'meaning', 'history', 'team_note', 'collage_finale',
        'inbound', 'outbound', 'return', 'location', 'contact',
        'hero', 'marquee', 'services', 'stats', 'about', 'value_prop', 'gallery', 'testimonials',
    ],
    'contact_channels' => ['whatsapp', 'instagram', 'email'],
    'services' => ['inbound', 'outbound', 'return'],
    'event_types' => [
        'session_start', 'section_view', 'contact_click', 'intro_view',
        'journey_start', 'story_meaning_view', 'story_history_view',
        'story_team_note_view', 'collage_complete', 'main_site_enter', 'service_view',
    ],
    'events_without_target' => [
        'session_start', 'intro_view', 'journey_start', 'story_meaning_view',
        'story_history_view', 'story_team_note_view', 'collage_complete', 'main_site_enter',
    ],
];
