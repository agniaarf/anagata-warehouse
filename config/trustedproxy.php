<?php

return [
    // Leave unset unless the app is behind a known reverse proxy. When set,
    // use only the proxy IPs/CIDRs that actually connect to the application.
    'proxies' => env('TRUSTED_PROXIES'),
];
