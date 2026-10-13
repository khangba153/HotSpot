<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceImage extends Model
{
    // Lấy ảnh của địa điểm theo thứ tự hiển thị (sort_order 1–3).
    public function findByPlaceId(int $placeId): array
    {
        if ($placeId <= 0) {
            return [];
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT image_id, image_path, sort_order
                     FROM place_images
                     WHERE place_id = :place_id
                     ORDER BY sort_order, image_id';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);
        $preparedStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $preparedStatement->execute();

        $imageList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

        return $imageList;
    }
}
