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
        $sql = 'SELECT places.place_id, places.title, places.created_at,
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

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
