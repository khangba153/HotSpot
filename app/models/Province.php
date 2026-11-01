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

        $preparedStatement->execute();

        $provinceList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

        $preparedStatement->closeCursor();

        return $provinceList;
    }
}
