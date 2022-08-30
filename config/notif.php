<?php

return [
    'default' => env('SN_DRIVER', 'rabbitmq'),
    'agent' => env('SN_AGENT', 'default'),
    'rabbitmq' => [
        'queue' => env('SN_BROKER_QUEUE', 'queue'),
        'host' => env('SN_BROKER_HOST', 'rabbitmq'),
        'vhost' => env('SN_BROKER_VHOST', '/'),
        'port' => env('SN_BROKER_PORT', 5672),
        'user' => env('SN_BROKER_USER', 'guest'),
        'password' => env('SN_BROKER_PASSWORD', 'guest'),
    ],
    'http' => [
        'base_url' => env('SN_BASE_URL', 'http://localhost:80'),
        'token' => env('SN_TOKEN', ''),
    ]
];
