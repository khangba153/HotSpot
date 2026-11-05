<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class Tag extends Model
{
    /**
     * Lấy danh sách nhãn có sẵn để tái sử dụng trong form địa điểm.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAllTags(): array
    {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT tag_id, name, slug
                     FROM tags
                     ORDER BY name, tag_id';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->execute();

            $tagList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

            return $tagList;
        } finally {
            $preparedStatement->closeCursor();
        }
    }
    /**
     * Lấy các nhãn đang hoạt động để hiển thị trong bộ lọc tìm kiếm.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findActiveTags(): array
    {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT tag_id, name, slug
                     FROM tags
                     WHERE is_active = 1
                     ORDER BY name, tag_id';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->execute();

            return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $preparedStatement->closeCursor();
        }
    }

    /**
     * Chỉ xác nhận slug thuộc nhãn đang hoạt động.
     */
    public function isActiveSlug(string $tagSlug): bool
    {
        $tagSlug = trim($tagSlug);

        if ($tagSlug === '' || strlen($tagSlug) > 100) {
            return false;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT 1
                     FROM tags
                     WHERE slug = :tag_slug
                       AND is_active = 1
                     LIMIT 1';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
            $preparedStatement->bindValue(':tag_slug', $tagSlug, PDO::PARAM_STR);
            $preparedStatement->execute();

            return $preparedStatement->fetchColumn() !== false;
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
