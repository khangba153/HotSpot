<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceSearch extends Model
{
    /**
     * Lấy tối đa 12 địa điểm công khai cho trang chủ.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findApprovedForHome(): array
    {
        $statement = self::getConnection()->prepare(
            'CALL PTH_SEARCH_APPROVED_PLACES(?, ?, ?, ?, ?, ?, ?)'
        );

        $statement->bindValue(1, null, PDO::PARAM_NULL);
        $statement->bindValue(2, null, PDO::PARAM_NULL);
        $statement->bindValue(3, null, PDO::PARAM_NULL);
        $statement->bindValue(4, null, PDO::PARAM_NULL);
        $statement->bindValue(5, null, PDO::PARAM_NULL);
        $statement->bindValue(6, 1, PDO::PARAM_INT);
        $statement->bindValue(7, 12, PDO::PARAM_INT);

        try {
            $statement->execute();
            $places = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Procedure còn trả về total_count; tiêu thụ mọi result set.
            while ($statement->nextRowset()) {
                $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            return array_slice($places, 0, 12);
        } finally {
            $statement->closeCursor();
        }
    }

    /**
     * Đọc danh mục đang hoạt động qua stored procedure có sẵn.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findActiveCategories(): array
    {
        $statement = self::getConnection()->prepare(
            'CALL PTH_LIST_ACTIVE_CATEGORIES()'
        );

        try {
            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_ASSOC);

            while ($statement->nextRowset()) {
                $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            return $categories;
        } finally {
            $statement->closeCursor();
        }
    }
}
