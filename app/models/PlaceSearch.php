<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class PlaceSearch extends Model
{
    /**
     * Adapter tạm thời giữ tương thích với caller cũ của TV3.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findActiveCategories(): array
    {
        $categoryModel = new Category();

        return $categoryModel->findActiveCategories();
    }

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
     * Tìm địa điểm APPROVED theo các bộ lọc và trang hiện tại.
     * Routine trả danh sách ở result set đầu và total_count ở result set thứ hai.
     *
     * Stored procedure trả danh sách địa điểm ở result set đầu tiên
     * và total_count ở result set thứ hai.
     *
     * @return array{places: array<int, array<string, mixed>>, total_count: int}
     */
    public function searchApprovedForHome(
        ?string $keyword = null,
        ?int $categoryId = null,
        ?string $provinceCode = null,
        ?string $wardCode = null,
        ?string $tagSlug = null,
        int $page = 1,
        int $pageSize = 12
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

        if ($wardCode !== null) {
            $wardCode = trim($wardCode);
        }

        if ($wardCode === '') {
            $wardCode = null;
        }

        if ($tagSlug !== null) {
            $tagSlug = trim($tagSlug);
        }

        if ($tagSlug === '') {
            $tagSlug = null;
        }

        $pageNumber = max(1, $page);
        $normalizedPageSize = min(max(1, $pageSize), 30);

        $databaseConnection = self::getConnection();
        $sqlQuery = 'CALL PTH_SEARCH_APPROVED_PLACES(?, ?, ?, ?, ?, ?, ?)';
        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        try {
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
            $preparedStatement->bindValue(
                4,
                $wardCode,
                $wardCode === null ? PDO::PARAM_NULL : PDO::PARAM_STR
            );
            $preparedStatement->bindValue(
                5,
                $tagSlug,
                $tagSlug === null ? PDO::PARAM_NULL : PDO::PARAM_STR
            );
            $preparedStatement->bindValue(6, $pageNumber, PDO::PARAM_INT);
            $preparedStatement->bindValue(7, $normalizedPageSize, PDO::PARAM_INT);

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
                'places' => $placeList,
                'total_count' => max(0, $totalCount),
            ];
        } finally {
            $preparedStatement->closeCursor();
        }
    }
}
