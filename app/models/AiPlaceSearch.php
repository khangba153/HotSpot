<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use RuntimeException;

final class AiPlaceSearch extends Model
{
    // Bộ lọc đã được Service kiểm tra; thứ tự SQL chỉ lấy từ danh sách cố định.
    public function search(array $filters): array
    {
        $sortOrders = [
            'default' => 'places.created_at DESC, places.place_id DESC',
            'rating_desc' => 'average_rating DESC, review_count DESC, favorite_count DESC, places.place_id DESC',
            'reviews_desc' => 'review_count DESC, average_rating DESC, places.place_id DESC',
            'favorites_desc' => 'favorite_count DESC, average_rating DESC, places.place_id DESC',
            'newest' => 'places.created_at DESC, places.place_id DESC',
            'oldest' => 'places.created_at ASC, places.place_id ASC',
            'updated_desc' => 'places.updated_at DESC, places.place_id DESC',
        ];
        if (!isset($sortOrders[$filters['sort']])) {
            throw new RuntimeException('Cách sắp xếp chưa được hỗ trợ.');
        }
        $conditions = ["places.status_code = 'APPROVED'"];
        $parameters = [];
        if ($filters['keyword'] !== null) {
            $keyword = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['keyword']);
            $conditions[] = "(places.title LIKE :keyword_title ESCAPE '!' OR places.description LIKE :keyword_description ESCAPE '!' OR places.address LIKE :keyword_address ESCAPE '!')";
            foreach (['keyword_title', 'keyword_description', 'keyword_address'] as $fieldName) {
                $parameters[$fieldName] = '%' . $keyword . '%';
            }
        }
        if ($filters['category_id'] !== null) {
            $conditions[] = 'places.category_id = :category_id';
            $parameters['category_id'] = $filters['category_id'];
        }
        if ($filters['province_code'] !== null) {
            $conditions[] = 'places.province_code = :province_code';
            $parameters['province_code'] = $filters['province_code'];
        }
        // Mỗi EXISTS kiểm một nhãn để kết quả phải có đủ các nhãn đã yêu cầu.
        foreach ($filters['tag_slugs'] as $index => $tagSlug) {
            $parameterName = 'tag_' . $index;
            $conditions[] = 'EXISTS (SELECT 1 FROM place_tags INNER JOIN tags ON tags.tag_id=place_tags.tag_id '
                . 'WHERE place_tags.place_id=places.place_id AND tags.is_active=1 AND tags.slug=:' . $parameterName . ')';
            $parameters[$parameterName] = $tagSlug;
        }
        if ($filters['min_rating'] !== null) {
            $conditions[] = 'review_stats.average_rating >= :min_rating';
            $parameters['min_rating'] = (string) $filters['min_rating'];
        }
        if ($filters['min_reviews'] !== null) {
            $conditions[] = 'COALESCE(review_stats.review_count,0) >= :min_reviews';
            $parameters['min_reviews'] = $filters['min_reviews'];
        }
        if ($filters['min_favorites'] !== null) {
            $conditions[] = 'COALESCE(favorite_stats.favorite_count,0) >= :min_favorites';
            $parameters['min_favorites'] = $filters['min_favorites'];
        }
        if ($filters['created_within_days'] !== null) {
            $conditions[] = 'places.created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL :created_within_days DAY) AND places.created_at <= CURRENT_TIMESTAMP';
            $parameters['created_within_days'] = $filters['created_within_days'];
        }
        if ($filters['has_coordinates']) {
            $conditions[] = 'places.latitude IS NOT NULL AND places.longitude IS NOT NULL';
        }
        $selectSql = 'SELECT places.place_id, places.title, places.description, places.address, places.category_id, places.province_code, '
            . 'places.created_at, places.updated_at, places.latitude, places.longitude, categories.name AS category_name, provinces.province_name, '
            . '(SELECT place_images.image_path FROM place_images WHERE place_images.place_id=places.place_id ORDER BY place_images.sort_order,place_images.image_id LIMIT 1) AS cover_image, '
            . 'COALESCE(review_stats.average_rating,0) AS average_rating, '
            . 'COALESCE(review_stats.review_count,0) AS review_count, '
            . 'COALESCE(favorite_stats.favorite_count,0) AS favorite_count '
            . 'FROM places INNER JOIN categories ON categories.category_id=places.category_id '
            . 'INNER JOIN provinces ON provinces.province_code=places.province_code '
            . "LEFT JOIN (SELECT place_id,AVG(rating) AS average_rating,COUNT(*) AS review_count FROM reviews WHERE status_code='VISIBLE' GROUP BY place_id) review_stats ON review_stats.place_id=places.place_id "
            . 'LEFT JOIN (SELECT place_id,COUNT(*) AS favorite_count FROM favorites GROUP BY place_id) favorite_stats ON favorite_stats.place_id=places.place_id '
            . 'WHERE ' . implode(' AND ', $conditions);

        $connection = self::getConnection();
        $countStatement = $connection->prepare('SELECT COUNT(*) FROM (' . $selectSql . ') matched_places');
        try {
            $this->bindParameters($countStatement, $parameters);
            $countStatement->execute();
            $totalCount = (int) $countStatement->fetchColumn();
        } finally {
            $countStatement->closeCursor();
        }
        $statement = $connection->prepare($selectSql . ' ORDER BY ' . $sortOrders[$filters['sort']] . ' LIMIT :result_limit');
        try {
            $this->bindParameters($statement, $parameters);
            $statement->bindValue(':result_limit', $filters['limit'], PDO::PARAM_INT);
            $statement->execute();
            return ['places' => $statement->fetchAll(PDO::FETCH_ASSOC), 'total_count' => $totalCount];
        } finally {
            $statement->closeCursor();
        }
    }

    // Mỗi placeholder có tên riêng để chạy được với PDO prepare thật.
    private function bindParameters(\PDOStatement $statement, array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            $parameterType = PDO::PARAM_STR;
            if (is_int($value)) {
                $parameterType = PDO::PARAM_INT;
            }
            $statement->bindValue(':' . $name, $value, $parameterType);
        }
    }
}
