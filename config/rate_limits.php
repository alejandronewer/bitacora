<?php

return [
    'api' => [
        // En produccion: habilitado por defecto. En local: deshabilitado por defecto.
        'enabled' => (bool) env('RATE_LIMIT_API_ENABLED', env('APP_ENV') === 'production'),
        'max_attempts' => (int) env('RATE_LIMIT_API_MAX_ATTEMPTS', env('APP_ENV') === 'production' ? 60 : 6000),
        'decay_minutes' => (int) env('RATE_LIMIT_API_DECAY_MINUTES', 1),
    ],

    'login' => [
        // En produccion: habilitado por defecto. En local: deshabilitado por defecto.
        'enabled' => (bool) env('RATE_LIMIT_LOGIN_ENABLED', env('APP_ENV') === 'production'),
        'max_attempts' => (int) env('RATE_LIMIT_LOGIN_MAX_ATTEMPTS', env('APP_ENV') === 'production' ? 5 : 50),
        'decay_seconds' => (int) env('RATE_LIMIT_LOGIN_DECAY_SECONDS', 60),
    ],
];
