<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceSearch extends Model
{
    /**
     * Giữ lại API hiện có cho các nơi đang cần danh sách địa điểm trang chủ.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findApprovedForHome(): array
    {
        $result = $this->searchApprovedForHome();

        return $result['places'];
    }

    /**
     * Tìm địa điểm đã được duyệt trên Home và lấy tổng số khớp.
     *
     * Keyword được truyền qua parameter của stored procedure, không ghép SQL.
     * Procedure hiện dùng 7 tham số và trả result set địa điểm trước,
     * result set total_count sau; vẫn tiêu thụ mọi result set trước closeCursor().
     *
     * @return array{places: array<int, array<string, mixed>>, total_count: int}
     */
    public function searchApprovedForHome(?string $keyword = null): array
    {
        $keyword = $keyword !== null ? trim($keyword) : null;
        if ($keyword === '') {
            $keyword = null;
        }

        $statement = self::getConnection()->prepare(
            'CALL PTH_SEARCH_APPROVED_PLACES(?, ?, ?, ?, ?, ?, ?)'
        );

        $statement->bindValue(
            1,
            $keyword,
            $keyword === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $statement->bindValue(2, null, PDO::PARAM_NULL);
        $statement->bindValue(3, null, PDO::PARAM_NULL);
        $statement->bindValue(4, null, PDO::PARAM_NULL);
        $statement->bindValue(5, null, PDO::PARAM_NULL);
        $statement->bindValue(6, 1, PDO::PARAM_INT);
        $statement->bindValue(7, 12, PDO::PARAM_INT);

        try {
            $statement->execute();
            $places = $statement->fetchAll(PDO::FETCH_ASSOC);
            $totalCount = count($places);

            // Một số procedure có thể trả total_count cùng result set địa điểm.
            if (isset($places[0]['total_count']) && is_numeric($places[0]['total_count'])) {
                $totalCount = (int) $places[0]['total_count'];
            }

            // Routine hiện trả thêm result set tổng số; phải đọc hết để PDO tái sử dụng kết nối.
            while ($statement->nextRowset()) {
                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                if (isset($rows[0]['total_count']) && is_numeric($rows[0]['total_count'])) {
                    $totalCount = (int) $rows[0]['total_count'];
                }
            }

            return [
                'places' => array_slice($places, 0, 12),
                'total_count' => max(0, $totalCount),
            ];
        } finally {
            $statement->closeCursor();
        }
    }

    /**
     * Đọc danh mục đang hoạt động qua stored procedure có sẵn.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findActiveCategories(): array
    {
        $statement = self::getConnection()->prepare(
            'CALL PTH_LIST_ACTIVE_CATEGORIES()'
        );

        try {
            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_ASSOC);

            while ($statement->nextRowset()) {
                $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            return $categories;
        } finally {
            $statement->closeCursor();
        }
    }
}
