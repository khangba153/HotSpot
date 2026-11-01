<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\Category;
use HotSpot\Models\PlaceSearch;
use HotSpot\Models\Province;
use Throwable;

final class PlaceSearchController extends Controller
{
    public function search(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $rawKeyword = $_GET['keyword'] ?? '';
            $keyword = is_string($rawKeyword) ? trim($rawKeyword) : '';
            $normalizedKeyword = preg_replace('/\\s+/u', ' ', $keyword);

            if ($normalizedKeyword !== null) {
                $keyword = $normalizedKeyword;
            }

            if (function_exists('mb_substr')) {
                $keyword = mb_substr($keyword, 0, 100, 'UTF-8');
            } else {
                $keywordCharacters = preg_split(
                    '//u',
                    $keyword,
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );

                if (is_array($keywordCharacters)) {
                    $keyword = implode('', array_slice($keywordCharacters, 0, 100));
                } else {
                    $keyword = substr($keyword, 0, 100);
                }
            }

            $categoryModel = new Category();
            $provinceModel = new Province();

            $categoryList = $categoryModel->findActiveCategories();
            $provinceList = $provinceModel->findAllProvinces();

            $categoryId = $this->readCategoryId($categoryList);
            $provinceCode = $this->readProvinceCode($provinceList);

            $placeSearchModel = new PlaceSearch();

            $searchResult = $placeSearchModel->searchApprovedForHome(
                $keyword !== '' ? $keyword : null,
                $categoryId,
                $provinceCode
            );

            $places = $searchResult['places'];

            ob_start();

            try {
                require dirname(__DIR__) . '/views/home/_place_cards.php';
                $html = ob_get_clean();
            } catch (Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Tìm kiếm thành công.',
                'data' => [
                    'html' => $html,
                    'total_count' => $searchResult['total_count'],
                    'keyword' => $keyword,
                    'category_id' => $categoryId,
                    'province_code' => $provinceCode,
                ],
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $exception) {
            error_log(
                'PlaceSearch API error: ' . $exception->getMessage()
            );

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Không thể tìm kiếm địa điểm lúc này.',
                'data' => null,
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Chỉ chấp nhận category_id thuộc danh sách danh mục đang hoạt động.
     *
     * @param array<int, array<string, mixed>> $categoryList
     */
    private function readCategoryId(array $categoryList): ?int
    {
        $rawCategoryId = $_GET['category_id'] ?? '';

        if (
            is_string($rawCategoryId) === false
            || $rawCategoryId === ''
            || ctype_digit($rawCategoryId) === false
        ) {
            return null;
        }

        $requestedCategoryId = (int) $rawCategoryId;

        if ($requestedCategoryId <= 0) {
            return null;
        }

        foreach ($categoryList as $categoryInformation) {
            if ((int) $categoryInformation['category_id'] === $requestedCategoryId) {
                return $requestedCategoryId;
            }
        }

        return null;
    }

    /**
     * Chỉ chấp nhận province_code có trong database và giữ nguyên dạng string.
     *
     * @param array<int, array<string, mixed>> $provinceList
     */
    private function readProvinceCode(array $provinceList): ?string
    {
        $rawProvinceCode = $_GET['province_code'] ?? '';

        if (is_string($rawProvinceCode) === false) {
            return null;
        }

        $requestedProvinceCode = trim($rawProvinceCode);

        if ($requestedProvinceCode === '' || strlen($requestedProvinceCode) > 10) {
            return null;
        }

        foreach ($provinceList as $provinceInformation) {
            $availableProvinceCode = (string) $provinceInformation['province_code'];

            if ($availableProvinceCode === $requestedProvinceCode) {
                return $requestedProvinceCode;
            }
        }

        return null;
    }
}
