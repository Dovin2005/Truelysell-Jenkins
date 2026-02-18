<?php

return [
    'test' => [
        'sk' => env('STRIPE_SECRET'),
        'pk' =>env('STRIPE_KEY'),
    ],
    'live' => [
        'sk' => env('STRIPE_SECRET'),
        'pk' => env('STRIPE_KEY'),
    ],
];