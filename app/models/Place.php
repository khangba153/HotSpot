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
        $this->bindPlaceData($preparedStatement, $placeData);

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

    // Khóa bài trước khi kiểm quyền ở POST để trạng thái không đổi giữa lúc kiểm tra và lưu.
    public function lockById(int $placeId): ?array
    {
        $databaseConnection = self::getConnection();
        if ($databaseConnection->inTransaction() === false) {
            throw new RuntimeException('Phải mở giao dịch trước khi khóa địa điểm.');
        }
        $statement = $databaseConnection->prepare('SELECT * FROM places WHERE place_id = :place_id FOR UPDATE');
        $statement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        try {
            $statement->execute();
            $place = $statement->fetch(PDO::FETCH_ASSOC);
        } finally {
            $statement->closeCursor();
        }
        if ($place === false) {
            return null;
        }
        return $place;
    }

    // Sửa nội dung giữ nguyên trạng thái; gửi lại mới dùng routine chuyển về PENDING.
    public function updateOwnedPlace(int $ownerId, array $lockedPlace, array $placeData, bool $resubmit): void
    {
        $databaseConnection = self::getConnection();
        if ($databaseConnection->inTransaction() === false) {
            throw new RuntimeException('Phải mở giao dịch trước khi sửa địa điểm.');
        }
        $this->validateEditReferences($placeData, (int) $lockedPlace['category_id']);
        if ($resubmit) {
            $sqlQuery = 'CALL CTH_RESUBMIT_PLACE(:owner_id, :place_id, :category_id, :province_code,
                :ward_code, :title, :description, :address, :latitude, :longitude)';
        } else {
            $sqlQuery = "UPDATE places SET category_id = :category_id, province_code = :province_code,
                ward_code = :ward_code, title = :title, description = :description, address = :address,
                latitude = :latitude, longitude = :longitude
                WHERE place_id = :place_id AND owner_id = :owner_id AND status_code IN ('PENDING', 'REJECTED')";
        }
        $statement = $databaseConnection->prepare($sqlQuery);
        $statement->bindValue(':owner_id', $ownerId, PDO::PARAM_INT);
        $statement->bindValue(':place_id', (int) $lockedPlace['place_id'], PDO::PARAM_INT);
        $this->bindPlaceData($statement, $placeData);
        try {
            $statement->execute();
            while ($statement->nextRowset()) {
            }
        } finally {
            $statement->closeCursor();
        }
    }

    // Cho giữ danh mục cũ đã ngừng, nhưng không được chuyển sang danh mục không hoạt động khác.
    private function validateEditReferences(array $placeData, int $oldCategoryId): void
    {
        $databaseConnection = self::getConnection();
        $statement = $databaseConnection->prepare('SELECT category_id FROM categories
            WHERE category_id = ? AND (is_active = 1 OR category_id = ?) FOR UPDATE');
        try {
            $statement->execute([$placeData['category_id'], $oldCategoryId]);
            if ($statement->fetchColumn() === false) {
                throw new \InvalidArgumentException('Danh mục đã chọn không khả dụng.');
            }
        } finally {
            $statement->closeCursor();
        }
        $statement = $databaseConnection->prepare('SELECT province_code FROM provinces
            WHERE province_code = ? AND is_active = 1 FOR UPDATE');
        try {
            $statement->execute([$placeData['province_code']]);
            if ($statement->fetchColumn() === false) {
                throw new \InvalidArgumentException('Tỉnh/thành đã chọn không khả dụng.');
            }
        } finally {
            $statement->closeCursor();
        }
        if ($placeData['ward_code'] !== null) {
            $statement = $databaseConnection->prepare('SELECT ward_code FROM wards
                WHERE ward_code = ? AND province_code = ? AND is_active = 1 FOR UPDATE');
            try {
                $statement->execute([$placeData['ward_code'], $placeData['province_code']]);
                if ($statement->fetchColumn() === false) {
                    throw new \InvalidArgumentException('Phường/xã không thuộc tỉnh/thành đã chọn.');
                }
            } finally {
                $statement->closeCursor();
            }
        }
    }

    // Dùng chung tham số cho tạo mới, sửa và gửi duyệt lại.
    private function bindPlaceData(\PDOStatement $statement, array $placeData): void
    {
        $statement->bindValue(':category_id', $placeData['category_id'], PDO::PARAM_INT);
        foreach (['province_code', 'title', 'description', 'address'] as $fieldName) {
            $statement->bindValue(':' . $fieldName, $placeData[$fieldName], PDO::PARAM_STR);
        }
        foreach (['ward_code', 'latitude', 'longitude'] as $optionalField) {
            if ($placeData[$optionalField] === null) {
                $statement->bindValue(':' . $optionalField, null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':' . $optionalField, $placeData[$optionalField], PDO::PARAM_STR);
            }
        }
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
