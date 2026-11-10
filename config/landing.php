<?php

return [
    // Deploy the new files safely before explicitly switching the public homepage.
    'anagata_enabled' => env('ANAGATA_LANDING_ENABLED', env('FIG'.'MA_LANDING_ENABLED', env('APP_ENV', 'production') !== 'production')),
];
