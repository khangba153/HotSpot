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
        $searchResult = $this->searchApprovedForHome();

        return $searchResult['places'];
    }

    /**
     * Tìm địa điểm APPROVED theo keyword, danh mục và tỉnh/thành.
     *
     * Stored procedure trả danh sách địa điểm ở result set đầu tiên
     * và total_count ở result set thứ hai.
     *
     * @return array{places: array<int, array<string, mixed>>, total_count: int}
     */
    public function searchApprovedForHome(
        ?string $keyword = null,
        ?int $categoryId = null,
        ?string $provinceCode = null
    ): array {
        if ($keyword !== null) {
            $keyword = trim($keyword);
        }

        if ($keyword === '') {
            $keyword = null;
        }

        if ($categoryId !== null && $categoryId <= 0) {
            $categoryId = null;
        }

        if ($provinceCode !== null) {
            $provinceCode = trim($provinceCode);
        }

        if ($provinceCode === '') {
            $provinceCode = null;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'CALL PTH_SEARCH_APPROVED_PLACES(?, ?, ?, ?, ?, ?, ?)';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            1,
            $keyword,
            $keyword === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );

        $preparedStatement->bindValue(
            2,
            $categoryId,
            $categoryId === null ? PDO::PARAM_NULL : PDO::PARAM_INT
        );

        $preparedStatement->bindValue(
            3,
            $provinceCode,
            $provinceCode === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );

        $preparedStatement->bindValue(4, null, PDO::PARAM_NULL);
        $preparedStatement->bindValue(5, null, PDO::PARAM_NULL);
        $preparedStatement->bindValue(6, 1, PDO::PARAM_INT);
        $preparedStatement->bindValue(7, 12, PDO::PARAM_INT);

        try {
            $preparedStatement->execute();

            $placeList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
            $totalCount = count($placeList);

            while ($preparedStatement->nextRowset()) {
                $totalCountRows = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

                if (
                    isset($totalCountRows[0]['total_count'])
                    && is_numeric($totalCountRows[0]['total_count'])
                ) {
                    $totalCount = (int) $totalCountRows[0]['total_count'];
                }
            }

            return [
                'places' => array_slice($placeList, 0, 12),
                'total_count' => max(0, $totalCount),
            ];
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
