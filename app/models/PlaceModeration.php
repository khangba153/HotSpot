<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceModeration extends Model
{
    public function setPendingPlaceStatus(
        int $adminId,
        int $placeId,
        string $newStatus,
        ?string $rejectionReason
    ): void {
        $connection = self::getConnection();
        $connection->beginTransaction();

        try {
            $placeStatement = $connection->prepare(
                'SELECT status_code FROM places WHERE place_id = :place_id FOR UPDATE'
            );
            $placeStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
            $placeStatement->execute();
            $place = $placeStatement->fetch(PDO::FETCH_ASSOC);
            $placeStatement->closeCursor();

            if ($place === false || $place['status_code'] !== 'PENDING') {
                throw new \RuntimeException('Địa điểm không còn ở trạng thái chờ duyệt.');
            }

            if ($newStatus === 'APPROVED') {
                $imageStatement = $connection->prepare(
                    'SELECT image_path FROM place_images WHERE place_id = :place_id ORDER BY sort_order, image_id'
                );
                $imageStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
                $imageStatement->execute();
                $images = $imageStatement->fetchAll(PDO::FETCH_ASSOC);
                $imageStatement->closeCursor();

                if (count($images) < 1 || count($images) > 3) {
                    throw new \RuntimeException('Bài đăng cần có từ 1 đến 3 ảnh hợp lệ trước khi duyệt.');
                }
                foreach ($images as $image) {
                    $imagePath = trim((string) $image['image_path']);
                    $isLocalImage = preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $imagePath) === 1
                        && strpos($imagePath, '..') === false
                        && is_file(dirname(__DIR__, 2) . '/public/' . $imagePath);
                    $isRemoteImage = preg_match('#^https?://#i', $imagePath) === 1
                        && filter_var($imagePath, FILTER_VALIDATE_URL) !== false;
                    if (!$isLocalImage && !$isRemoteImage) {
                        throw new \RuntimeException('Bài đăng có đường dẫn ảnh không hợp lệ.');
                    }
                }
            }

            $procedure = $connection->prepare(
                'CALL HTH_SET_PLACE_STATUS(:admin_id, :place_id, :new_status, :rejection_reason)'
            );
            $procedure->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
            $procedure->bindValue(':place_id', $placeId, PDO::PARAM_INT);
            $procedure->bindValue(':new_status', $newStatus, PDO::PARAM_STR);
            if ($rejectionReason === null) {
                $procedure->bindValue(':rejection_reason', null, PDO::PARAM_NULL);
            } else {
                $procedure->bindValue(':rejection_reason', $rejectionReason, PDO::PARAM_STR);
            }
            $procedure->execute();
            while ($procedure->nextRowset()) {
            }
            $procedure->closeCursor();

            // Ghi nhận lần duyệt đầu; restore về APPROVED không được thay ngày cũ.
            if ($newStatus === 'APPROVED') {
                $approvedAt = $connection->prepare(
                    'UPDATE places SET approved_at = CURRENT_TIMESTAMP WHERE place_id = :place_id AND approved_at IS NULL'
                );
                $approvedAt->bindValue(':place_id', $placeId, PDO::PARAM_INT);
                $approvedAt->execute();
                $approvedAt->closeCursor();
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function findPendingPlaces(): array
    {
        $sql = 'SELECT places.place_id, places.category_id, places.title, places.created_at,
                       (SELECT place_images.image_path FROM place_images
                        WHERE place_images.place_id = places.place_id
                        ORDER BY place_images.sort_order, place_images.image_id LIMIT 1) AS image_path,
                       users.full_name AS owner_name,
                       categories.name AS category_name,
                       provinces.province_name
                FROM places
                INNER JOIN users ON users.user_id = places.owner_id
                INNER JOIN categories ON categories.category_id = places.category_id
                INNER JOIN provinces ON provinces.province_code = places.province_code
                INNER JOIN place_statuses ON place_statuses.status_code = places.status_code
                WHERE place_statuses.status_code = :status_code
                ORDER BY places.created_at DESC, places.place_id DESC';

        $statement = self::getConnection()->prepare($sql);
        $statement->bindValue(':status_code', 'PENDING', PDO::PARAM_STR);
        $statement->execute();

        $places = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $places;
    }

    public function findPendingDetail(int $placeId): ?array
    {
        $sql = 'SELECT places.place_id, places.owner_id, places.category_id, places.title, places.description,
                       places.address, places.created_at, places.status_code,
                       places.latitude, places.longitude, places.rejection_reason,
                       categories.name AS category_name, provinces.province_name,
                       wards.ward_name, users.full_name AS owner_name, users.email AS owner_email
                FROM places
                INNER JOIN users ON users.user_id = places.owner_id
                INNER JOIN categories ON categories.category_id = places.category_id
                INNER JOIN provinces ON provinces.province_code = places.province_code
                LEFT JOIN wards ON wards.ward_code = places.ward_code
                WHERE places.place_id = :place_id AND places.status_code = :status_code
                LIMIT 1';
        $statement = self::getConnection()->prepare($sql);
        $statement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $statement->bindValue(':status_code', 'PENDING', PDO::PARAM_STR);
        $statement->execute();
        $place = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        if ($place === false) {
            return null;
        }
        $place['images'] = (new PlaceImage())->findByPlaceId($placeId);
        return $place;
    }
}
