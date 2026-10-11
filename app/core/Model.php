<?php
declare(strict_types=1);

namespace HotSpot\Core;

use PDO;

abstract class Model
{
    protected static function getConnection(): PDO
    {
        return Database::getConnection();
    }
}