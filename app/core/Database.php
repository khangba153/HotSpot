<?php
declare(strict_types=1);

namespace HotSpot\Core;

use PDO;
use RuntimeException;

final class Database
{
    // ?PDO nghĩa là thuộc tính có thể chứa đối tượng PDO hoặc null.
    private static ?PDO $databaseConnection = null;

    public static function getConnection(): PDO
    {
        if (self::$databaseConnection !== null) {
            return self::$databaseConnection;
        }

        $configurationFilePath = dirname(__DIR__) . '/config/database.php';

        if (is_file($configurationFilePath) === false) {
            throw new RuntimeException('Thiếu cấu hình database.');
        }

        $databaseConfiguration = require $configurationFilePath;

        if (is_array($databaseConfiguration) === false) {
            throw new RuntimeException('Cấu hình database không hợp lệ.');
        }

        $requiredConfigurationKeys = [
            'host', 'port', 'database', 'username', 'password',
        ];

        foreach ($requiredConfigurationKeys as $configurationKey) {
            if (array_key_exists($configurationKey, $databaseConfiguration) === false) {
                throw new RuntimeException('Cấu hình database chưa đầy đủ.');
            }
        }

        if (extension_loaded('pdo_mysql') === false) {
            throw new RuntimeException('PHP chưa bật pdo_mysql.');
        }

        $databaseHost = $databaseConfiguration['host'];
        $databasePort = (int) $databaseConfiguration['port'];
        $databaseName = $databaseConfiguration['database'];
        $databaseUsername = $databaseConfiguration['username'];
        $databasePassword = $databaseConfiguration['password'];

        $connectionString = 'mysql:host=' . $databaseHost;
        $connectionString = $connectionString . ';port=' . $databasePort;
        $connectionString = $connectionString . ';dbname=' . $databaseName;
        $connectionString = $connectionString . ';charset=utf8mb4';

        $connectionOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (isset($databaseConfiguration['ssl_ca'])) {
            $certificateAuthorityFilePath = $databaseConfiguration['ssl_ca'];

            if ($certificateAuthorityFilePath !== '') {
                $connectionOptions[PDO::MYSQL_ATTR_SSL_CA] = $certificateAuthorityFilePath;
            }
        }

        self::$databaseConnection = new PDO(
            $connectionString,
            $databaseUsername,
            $databasePassword,
            $connectionOptions
        );

        return self::$databaseConnection;
    }
}