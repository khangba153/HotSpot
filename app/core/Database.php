<?php
declare(strict_types=1);
namespace HotSpot\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;
    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $path = dirname(__DIR__) . '/config/database.php';
        if (!is_file($path)) {
            throw new RuntimeException('Chưa tạo app/config/database.php từ file ví dụ.');
        }
        $c = require $path;
        if (!is_array($c) || empty($c['database']) || empty($c['username'])) {
            throw new RuntimeException('Cấu hình database chưa đầy đủ.');
        }
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('PHP chưa bật extension pdo_mysql.');
        }
        $host = (string) ($c['host'] ?? '127.0.0.1');
        $port = (int) ($c['port'] ?? 3306);
        if ($port < 1 || $port > 65535) {
            throw new RuntimeException('DB port không hợp lệ.');
        }
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $c['database']);
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 5];
        if (!empty($c['ssl_ca'])) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = (string) $c['ssl_ca'];
        }
        self::$pdo = new PDO($dsn, (string) $c['username'], (string) ($c['password'] ?? ''), $options);
        self::$pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'");
        self::$pdo->exec("SET time_zone='+00:00'");
        return self::$pdo;
    }
}
