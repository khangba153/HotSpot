<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class Ward extends Model
{
    public function findActiveByProvinceCode(string $provinceCode): array
    {
        $databaseConnection = self::getConnection();
        $sqlQuery = 'CALL PTH_LIST_WARDS_BY_PROVINCE(:province_code)';
        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->bindValue(':province_code', $provinceCode, PDO::PARAM_STR);
            $preparedStatement->execute();

            $wardList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

            while ($preparedStatement->nextRowset()) {
                $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
            }

            return $wardList;
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
