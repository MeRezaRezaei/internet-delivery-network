<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tailscale API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Tailscale API integration.
    | You can obtain your Client ID and Client Secret from the Tailscale
    | Admin Console under Settings > OAuth Clients.
    |
    */

    'client_id' => env('TAILSCALE_CLIENT_ID'),
    'client_secret' => env('TAILSCALE_CLIENT_SECRET'),
    'tailnet' => env('TAILSCALE_TAILNET'),

    'base_url' => 'https://api.tailscale.com/api/v2/',
];
