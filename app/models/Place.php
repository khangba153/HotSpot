<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use RuntimeException;

final class Place extends Model
{
    // Tạo bài PENDING trong giao dịch do Controller quản lý cùng ảnh và nhãn.
    public function submitPendingPlace(int $ownerId, array $placeData): int
    {
        $databaseConnection = self::getConnection();
        if ($databaseConnection->inTransaction() === false) {
            throw new RuntimeException('Phải mở giao dịch trước khi lưu địa điểm.');
        }

        $preparedStatement = $databaseConnection->prepare(
            'CALL CTH_SUBMIT_PLACE(:owner_id, :category_id, :province_code, :ward_code,
                :title, :description, :address, :latitude, :longitude)'
        );
        $preparedStatement->bindValue(':owner_id', $ownerId, PDO::PARAM_INT);
        $preparedStatement->bindValue(':category_id', $placeData['category_id'], PDO::PARAM_INT);
        $preparedStatement->bindValue(':province_code', $placeData['province_code'], PDO::PARAM_STR);
        $preparedStatement->bindValue(':title', $placeData['title'], PDO::PARAM_STR);
        $preparedStatement->bindValue(':description', $placeData['description'], PDO::PARAM_STR);
        $preparedStatement->bindValue(':address', $placeData['address'], PDO::PARAM_STR);

        foreach (['ward_code', 'latitude', 'longitude'] as $optionalField) {
            if ($placeData[$optionalField] === null) {
                $preparedStatement->bindValue(':' . $optionalField, null, PDO::PARAM_NULL);
            } else {
                $preparedStatement->bindValue(':' . $optionalField, $placeData[$optionalField], PDO::PARAM_STR);
            }
        }

        try {
            $preparedStatement->execute();
            $placeId = (int) $preparedStatement->fetchColumn();
            while ($preparedStatement->nextRowset()) {
            }
        } finally {
            $preparedStatement->closeCursor();
        }

        if ($placeId <= 0) {
            throw new RuntimeException('Không nhận được mã địa điểm mới.');
        }

        return $placeId;
    }

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

    // Lấy danh sách địa điểm do một tài khoản đăng (TV3-05), mới nhất trước.
    public function findByOwnerId(int $ownerId): array
    {
        if ($ownerId <= 0) {
            return [];
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT
                        places.place_id,
                        places.owner_id,
                        places.category_id,
                        places.province_code,
                        places.ward_code,
                        places.status_code,
                        places.title,
                        places.description,
                        places.address,
                        places.rejection_reason,
                        places.created_at,
                        categories.name AS category_name,
                        provinces.province_name,
                        wards.ward_name,
                        place_statuses.display_name AS status_name,
                        (
                            SELECT place_images.image_path
                            FROM place_images
                            WHERE place_images.place_id = places.place_id
                            ORDER BY place_images.sort_order, place_images.image_id
                            LIMIT 1
                        ) AS cover_image
                     FROM places
                     INNER JOIN categories
                         ON categories.category_id = places.category_id
                     INNER JOIN provinces
                         ON provinces.province_code = places.province_code
                     LEFT JOIN wards
                         ON wards.ward_code = places.ward_code
                     INNER JOIN place_statuses
                         ON place_statuses.status_code = places.status_code
                     WHERE places.owner_id = :owner_id
                     ORDER BY places.created_at DESC, places.place_id DESC';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);
        $preparedStatement->bindValue(':owner_id', $ownerId, PDO::PARAM_INT);
        $preparedStatement->execute();

        $placeList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
        $preparedStatement->closeCursor();

        return $placeList;
    }
}
