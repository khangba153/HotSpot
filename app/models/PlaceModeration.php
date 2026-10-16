<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceModeration extends Model
{
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
