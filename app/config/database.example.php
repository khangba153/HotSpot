<?php
declare(strict_types=1);

// PDO's pdo_mysql driver is also used for the team's MariaDB server.
// Copy to database.php locally; never commit that file or credentials.
return [
    'host' => getenv('HOTSPOT_DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('HOTSPOT_DB_PORT') ?: 3306),
    'database' => getenv('HOTSPOT_DB_NAME') ?: 'hotspot_dev',
    'username' => getenv('HOTSPOT_DB_USER') ?: 'hotspot_app',
    'password' => getenv('HOTSPOT_DB_PASSWORD') ?: '',
    'ssl_ca' => getenv('HOTSPOT_DB_SSL_CA') ?: null,
];
