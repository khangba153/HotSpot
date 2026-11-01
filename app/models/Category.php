<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class Category extends Model
{
    /**
     * Lấy danh sách danh mục đang hoạt động từ database.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findActiveCategories(): array
    {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'CALL PTH_LIST_ACTIVE_CATEGORIES()';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->execute();

            $categoryList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

            while ($preparedStatement->nextRowset()) {
                $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
            }

            return $categoryList;
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
