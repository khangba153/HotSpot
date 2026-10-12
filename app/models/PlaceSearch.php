<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceSearch extends Model
{
    /**
     * Lấy tối đa 12 địa điểm công khai cho trang chủ.
     *
     * TV2_SEARCH_APPROVED_PLACES trả về hai result set: danh sách địa điểm
     * và tổng số kết quả. Cần tiêu thụ các result set còn lại trước khi
     * sử dụng lại kết nối PDO dùng chung.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findApprovedForHome(): array
    {
        $statement = self::getConnection()->prepare(
            'CALL TV2_SEARCH_APPROVED_PLACES(?, ?, ?, ?, ?, ?, ?)'
        );

        $statement->bindValue(1, null, PDO::PARAM_NULL);
        $statement->bindValue(2, null, PDO::PARAM_NULL);
        $statement->bindValue(3, null, PDO::PARAM_NULL);
        $statement->bindValue(4, null, PDO::PARAM_NULL);
        $statement->bindValue(5, null, PDO::PARAM_NULL);
        $statement->bindValue(6, 1, PDO::PARAM_INT);
        $statement->bindValue(7, 12, PDO::PARAM_INT);

        try {
            $statement->execute();
            $places = $statement->fetchAll(PDO::FETCH_ASSOC);

            while ($statement->nextRowset()) {
                // Đọc hết kết quả phụ (hiện tại là total_count) để giải phóng CALL.
                $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            return array_slice($places, 0, 12);
        } finally {
            $statement->closeCursor();
        }
    }
}
