<?php

return [
    'mode' => env('PAYU_MODE', 'test'),
    'merchant_key' => env('PAYU_MERCHANT_KEY'),
    'salt' => env('PAYU_MERCHANT_SALT'),
    'base_url' => env('PAYU_BASE_URL'),
];
