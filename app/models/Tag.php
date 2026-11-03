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
}
