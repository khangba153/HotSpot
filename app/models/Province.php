<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class Province extends Model
{
    /**
     * Lấy danh sách tỉnh/thành từ database.
     *
     * province_code luôn được giữ dưới dạng string để không mất số 0 đầu.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAllProvinces(): array
    {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT province_code, province_name
                     FROM provinces
                     ORDER BY province_name, province_code';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->execute();

            $provinceList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

            return $provinceList;
        } finally {
            $preparedStatement->closeCursor();
        }
    }

    public function existsProvinceCode(string $provinceCode): bool
    {
        $provinceCode = trim($provinceCode);

        if ($provinceCode === '' || strlen($provinceCode) > 10) {
            return false;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT 1
                     FROM provinces
                     WHERE province_code = :province_code
                     LIMIT 1';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->bindValue(':province_code', $provinceCode, PDO::PARAM_STR);
            $preparedStatement->execute();

            return $preparedStatement->fetchColumn() !== false;
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
