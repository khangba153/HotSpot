<?php

declare(strict_types=1);

// Sao chép thành app.php và điền cấu hình riêng; không commit app.php.
return [
    'google_client_id' => '',

    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'from_name' => 'Hot Spot',
    ],

    'app_url' => 'http://127.0.0.1:8000',
];
