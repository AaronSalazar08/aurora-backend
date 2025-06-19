<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    // Rutas a las que aplicar CORS (preflight + actual requests)
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Métodos HTTP permitidos en CORS
    'allowed_methods' => ['*'],

    // Orígenes permitidos (cliente React)
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    // Headers permitidos
    'allowed_headers' => ['*'],

    // Headers que expones al cliente (si los necesitas)
    'exposed_headers' => [],

    // Tiempo de cache para preflight (en segundos)
    'max_age' => 0,

    // Si usaras cookies en CORS, pon true. Con token Bearer déjalo false.
    'supports_credentials' => false,

];
