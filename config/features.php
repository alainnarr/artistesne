<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature flags
    |--------------------------------------------------------------------------
    |
    | Simple boolean toggles controlled via environment variables.
    | Add new flags here as the application grows.
    |
    */

    // Public artist directory (/artistes, /artistes/{slug}).
    // Enabled by default from V2 onward (new data model is now the source of truth).
    'artists_listing' => (bool) env('ARTISTS_LISTING', true),
];
