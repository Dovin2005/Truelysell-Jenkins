<?php

return [
    'key' => env('PAYMENT_GATEWAY_KEY'),
    'salt_32' => env('PAYMENT_GATEWAY_SALT_32'),
    'salt_256' => env('PAYMENT_GATEWAY_SALT_256'),
    'client_id' => env('PAYMENT_CLIENT_ID'),
    'client_secret' => env('PAYMENT_CLIENT_SECRET'),
];
