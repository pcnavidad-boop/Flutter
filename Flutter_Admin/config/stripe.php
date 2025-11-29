<?php

return [
    'secret' => env('STRIPE_SECRET'),
    'public' => env('STRIPE_PUBLIC_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
