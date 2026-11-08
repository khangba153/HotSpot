<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\Category;
use HotSpot\Models\PlaceSearch;
use HotSpot\Models\Province;
use HotSpot\Models\Tag;
use InvalidArgumentException;
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

            $categoryId = $this->readCategoryId();
            $provinceCode = $this->readProvinceCode();

            if ($categoryId !== null) {
                $categoryModel = new Category();

                if ($categoryModel->isActiveCategory($categoryId) === false) {
                    throw new InvalidArgumentException('Danh mục không hợp lệ.');
                }
            }

            if ($provinceCode !== null) {
                $provinceModel = new Province();

                if ($provinceModel->existsProvinceCode($provinceCode) === false) {
                    throw new InvalidArgumentException('Tỉnh/thành không hợp lệ.');
                }
            }

            $tagSlug = $this->readTagSlug();

            if ($tagSlug !== null) {
                $tagModel = new Tag();

                if ($tagModel->isActiveSlug($tagSlug) === false) {
                    throw new InvalidArgumentException('Nhãn không hợp lệ.');
                }
            }

            $pageNumber = $this->readPageNumber();
            $requestedPageNumber = $pageNumber;
            $pageSize = 12;
            $placeSearchModel = new PlaceSearch();
            $searchResult = $placeSearchModel->searchApprovedForHome(
                $keyword !== '' ? $keyword : null,
                $categoryId,
                $provinceCode,
                null,
                $tagSlug,
                $pageNumber,
                $pageSize
            );

            $totalPages = max(1, (int) ceil($searchResult['total_count'] / $pageSize));

            if ($pageNumber > $totalPages) {
                $pageNumber = $totalPages;
                $searchResult = $placeSearchModel->searchApprovedForHome(
                    $keyword !== '' ? $keyword : null,
                    $categoryId,
                    $provinceCode,
                    null,
                    $tagSlug,
                    $pageNumber,
                    $pageSize
                );
            }

            $categoryModel = new Category();
            $categoryCardLabels = $categoryModel->findCardLabelImages();
            $places = $searchResult['places'];
            $selectedCategoryIdForResponse = $categoryId;
            $selectedCategoryId = $categoryId === null ? '' : (string) $categoryId;

            ob_start();

            try {
                require dirname(__DIR__) . '/views/home/_place_cards.php';
                $html = ob_get_clean();
            } catch (Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }

            $page = $pageNumber;
            $selectedProvinceCode = $provinceCode;
            $selectedTagSlug = $tagSlug;

            ob_start();

            try {
                require dirname(__DIR__) . '/views/home/_pagination.php';
                $paginationHtml = ob_get_clean();
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
                    'category_id' => $selectedCategoryIdForResponse,
                    'province_code' => $provinceCode,
                    'tag_slug' => $tagSlug,
                    'page' => $pageNumber,
                    'requested_page' => $requestedPageNumber,
                    'page_size' => $pageSize,
                    'total_pages' => $totalPages,
                    'pagination_html' => $paginationHtml,
                ],
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => null,
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $exception) {
            error_log('PlaceSearch API error: ' . $exception->getMessage());

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
     * Chỉ nhận nhãn tồn tại và đang hoạt động.
     */
    private function readTagSlug(): ?string
    {
        $rawTagSlug = $_GET['tag_slug'] ?? '';

        if ($rawTagSlug === '') {
            return null;
        }

        if (is_string($rawTagSlug) === false) {
            throw new InvalidArgumentException('Nhãn không hợp lệ.');
        }

        $tagSlug = trim($rawTagSlug);

        if ($tagSlug === '') {
            return null;
        }

        if (strlen($tagSlug) > 100) {
            throw new InvalidArgumentException('Nhãn không hợp lệ.');
        }

        return $tagSlug;
    }

    /**
     * Đọc số trang từ query string; giá trị sai quay về trang đầu.
     */
    private function readPageNumber(): int
    {
        $rawPageNumber = $_GET['page'] ?? '1';

        if (
            is_string($rawPageNumber) === false
            || $rawPageNumber === ''
            || ctype_digit($rawPageNumber) === false
            || strlen($rawPageNumber) > 9
        ) {
            return 1;
        }

        $pageNumber = (int) $rawPageNumber;

        if ($pageNumber < 1) {
            return 1;
        }

        return $pageNumber;
    }

    private function readCategoryId(): ?int
    {
        $rawCategoryId = $_GET['category_id'] ?? '';

        if ($rawCategoryId === '') {
            return null;
        }

        if (
            is_string($rawCategoryId) === false
            || ctype_digit($rawCategoryId) === false
        ) {
            throw new InvalidArgumentException('Danh mục không hợp lệ.');
        }

        $categoryId = (int) $rawCategoryId;

        if ($categoryId <= 0) {
            throw new InvalidArgumentException('Danh mục không hợp lệ.');
        }

        return $categoryId;
    }

    private function readProvinceCode(): ?string
    {
        $rawProvinceCode = $_GET['province_code'] ?? '';

        if ($rawProvinceCode === '') {
            return null;
        }

        if (is_string($rawProvinceCode) === false) {
            throw new InvalidArgumentException('Tỉnh/thành không hợp lệ.');
        }

        $provinceCode = trim($rawProvinceCode);

        if ($provinceCode === '') {
            return null;
        }

        if (strlen($provinceCode) > 10) {
            throw new InvalidArgumentException('Tỉnh/thành không hợp lệ.');
        }

        return $provinceCode;
    }
}
