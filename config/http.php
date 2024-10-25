<?php

return [
    'middlewareGroups' => [
        'web' => [
            \App\Middleware\CheckCookieConsent::class,
            // Other middlewares...
        ],
    ],
];
