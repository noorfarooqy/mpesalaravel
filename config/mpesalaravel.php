<?php

return [


    'ctob' => [
        'routes' => [
            'validation_route' => env('MPESACTOB_VALIDATION_URL', '/mpesalaravel/api/v1/validation'),
            'confirmation_route' => env('MPESACTOB_CONFIRMATION_URL', '/mpesalaravel/api/v1/confirmation'),
        ],
        'account_length' => [10, 5],
        'model_verification' => env('MPESACTOB_MODEL_VERIFICATION', false),
        'verification_models' => [
            'class' => '',
            'comparison_fields' => [],
        ],
        'allow_new_accounts' => true,
    ],
    'btoc' => [],
];
