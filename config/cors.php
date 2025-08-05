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
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'user'],

    // Metode HTTP yang diizinkan untuk permintaan cross-origin.
    // Biasanya ini mencakup GET untuk mengambil data.
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    // Origin (URL) dari mana permintaan diizinkan.
    // Ganti dengan URL aplikasi React Anda. Jika React berjalan di port 3000:
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:3000',
    ], // Sesuaikan dengan port React Anda

    'allowed_origins_patterns' => [],

    // Header yang diizinkan dalam permintaan cross-origin.
    // '*' mengizinkan semua header, yang umum untuk pengembangan.
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
