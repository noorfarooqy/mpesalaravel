<?php

return [

    "onfon" => [
        "api_sender_id" => env('ONFON_SENDER_ID'),
        "api_username" => env("ONFON_API_USERNAME"),
        "api_password" => env("ONFON_API_PASSWORD"),
        "is_sandbox" => env("ONFON_IS_SANDBOX", true),
        "sandbox_url" => env("ONFON_SANDBOX_URL", "https://apis.onfonmedia.co.ke"),
        "production_url" => env("ONFON_PRODUCT_URL", "https://apis.onfonmedia.co.ke"),
        "dlr_callback" => env("ONFON_DLR_CALLBACK", "/v1/easy/onfon/callback"),
        "endpoints" => [
            "authorization" => [
                "endpoint" => "/v1/authorization",
                "method" => "POST",
            ],
            "balance" =>  [
                "endpoint" => "/v2_balance",
                "method" => "GET",
            ],
            "send_sms" => [
                "endpoint" => "/v2_send",
                "method" => "POST",
            ],
        ],
    ],
    'africastalking' => [
        'api_host' => env('AT_API_HOST', 'api.africastalking.com'),
        'api_url' => env('AT_URL', 'https://api.africastalking.com/version1'),
        'auth_endpoint' => '/auth-token/generate',
        'sms_endpoint' => '/version1/messaging',
        'api_key' => env('AT_API_KEY'),
        'username' => env('AT_USERNAME', 'sandbox'),

    ]
];
