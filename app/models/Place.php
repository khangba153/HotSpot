<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class Place extends Model
{
    // Lấy chi tiết một địa điểm kèm tên danh mục, tỉnh, phường và trạng thái.
    // Không lọc trạng thái ở đây; Controller quyết định ai được xem.
    public function findDetailById(int $placeId): ?array
    {
        if ($placeId <= 0) {
            return null;
        }

        $databaseConnection = self::getConnection();

        // Phường/xã là tùy chọn nên dùng LEFT JOIN với bảng wards.
        $sqlQuery = 'SELECT places.place_id,
                            places.owner_id,
                            places.category_id,
                            places.province_code,
                            places.ward_code,
                            places.status_code,
                            places.title,
                            places.description,
                            places.address,
                            places.latitude,
                            places.longitude,
                            places.rejection_reason,
                            places.created_at,
                            categories.name AS category_name,
                            provinces.province_name,
                            wards.ward_name,
                            place_statuses.display_name AS status_name
                     FROM places
                     INNER JOIN categories
                         ON categories.category_id = places.category_id
                     INNER JOIN provinces
                         ON provinces.province_code = places.province_code
                     INNER JOIN place_statuses
                         ON place_statuses.status_code = places.status_code
                     LEFT JOIN wards
                         ON wards.ward_code = places.ward_code
                     WHERE places.place_id = :place_id
                     LIMIT 1';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        // Truyền ID bằng tham số để tránh SQL injection.
        $preparedStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $preparedStatement->execute();

        $placeInformation = $preparedStatement->fetch(PDO::FETCH_ASSOC);

        if ($placeInformation === false) {
            return null;
        }

        return $placeInformation;
    }

    // Lấy danh sách nhãn đã gắn cho địa điểm, sắp xếp theo tên.
    public function findTagsByPlaceId(int $placeId): array
    {
        if ($placeId <= 0) {
            return [];
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT tags.tag_id, tags.name
                     FROM place_tags
                     INNER JOIN tags
                         ON tags.tag_id = place_tags.tag_id
                     WHERE place_tags.place_id = :place_id
                     ORDER BY tags.name';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);
        $preparedStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $preparedStatement->execute();

        $tagList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

        return $tagList;
    }
}
