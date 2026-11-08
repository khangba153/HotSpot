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

    // Giữ nhãn cho cả địa điểm thuộc danh mục đã ngừng nhận bài mới.
    public function findCardLabelImages(): array
    {
        $preparedStatement = self::getConnection()->prepare(
            'SELECT category_id, card_label_image_path FROM categories'
        );
        try {
            $preparedStatement->execute();
            $categoryList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
            $labelImages = [];
            foreach ($categoryList as $categoryInformation) {
                $categoryId = (int) $categoryInformation['category_id'];
                $labelImages[$categoryId] = trim((string) $categoryInformation['card_label_image_path']);
            }
            return $labelImages;
        } finally {
            $preparedStatement->closeCursor();
        }
    }

    public function isActiveCategory(int $categoryId): bool
    {
        if ($categoryId <= 0) {
            return false;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT 1
                     FROM categories
                     WHERE category_id = :category_id
                       AND is_active = 1
                     LIMIT 1';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
            $preparedStatement->execute();

            return $preparedStatement->fetchColumn() !== false;
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
