<?php

return [
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'payment_url' => env('PAYSTACK_PAYMENT_URL'),
    'callback_url' => env('PAYSTACK_CALLBACK_URL'),
];
