<?php

return [
    'api_login_id' => env('AUTHORIZE_NET_API_LOGIN_ID'),
    'transaction_key' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
    'environment' => env('AUTHORIZE_NET_ENV', 'sandbox'),
];
