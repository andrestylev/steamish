<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the Stripe API keys and webhook secret for the
    | application. These values should be set in your .env file.
    |
    */

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
