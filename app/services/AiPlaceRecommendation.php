<?php

declare(strict_types=1);

namespace HotSpot\Services;

use InvalidArgumentException;
use HotSpot\Models\Category;
use HotSpot\Models\Province;
use HotSpot\Models\Tag;
use RuntimeException;
use HotSpot\Models\PlaceSearch;
use HotSpot\Core\ClaudeClient;
use HotSpot\Core\AiSummaryCache;

final class AiPlaceRecommendation
{
    // Kiểm tra và chuẩn hóa yêu cầu trước khi gửi cho AI.
    public function validateRequest(string $requestText): string
    {
        if (mb_check_encoding($requestText, 'UTF-8') === false) {
            throw new InvalidArgumentException(
                'Nội dung tìm kiếm không đúng định dạng.'
            );
        }

        $requestText = trim($requestText);

        if ($requestText === '') {
            throw new InvalidArgumentException(
                'Vui lòng nhập nhu cầu tìm địa điểm.'
            );
        }

        if (mb_strlen($requestText, 'UTF-8') > 500) {
            throw new InvalidArgumentException(
                'Nội dung tìm kiếm tối đa 500 ký tự.'
            );
        }

        $normalizedText = preg_replace('/\s+/u', ' ', $requestText);

        if ($normalizedText === null) {
            throw new InvalidArgumentException(
                'Không thể xử lý nội dung tìm kiếm.'
            );
        }

        return $normalizedText;
    }

    // Lấy các lựa chọn có trong database để AI nhận diện bộ lọc.
    public function getSearchCatalog(): array
    {
        $categoryModel = new Category();
        $provinceModel = new Province();
        $tagModel = new Tag();

        $categories = $categoryModel->findActiveCategories();
        $provinces = $provinceModel->findAllProvinces();
        $tags = $tagModel->findActiveTags();

        return [
            'categories' => $categories,
            'provinces' => $provinces,
            'tags' => $tags,
        ];
    }

    // Đọc JSON và kiểm tra các trường bộ lọc bắt buộc.
    public function decodeSearchFilters(string $responseText): array
    {
        $responseText = trim($responseText);

        if ($responseText === '' || strlen($responseText) > 4000) {
            throw new RuntimeException(
                'Phản hồi bộ lọc từ AI không hợp lệ.'
            );
        }

        $searchFilters = json_decode($responseText, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
            || is_array($searchFilters) === false
            || array_is_list($searchFilters)
        ) {
            throw new RuntimeException(
                'AI chưa trả về bộ lọc đúng định dạng JSON.'
            );
        }

        $requiredFields = [
            'keyword',
            'category_id',
            'province_code',
            'tag_slug',
            'unsupported',
        ];

        $receivedFields = array_keys($searchFilters);
        $missingFields = array_diff($requiredFields, $receivedFields);
        $unexpectedFields = array_diff($receivedFields, $requiredFields);

        if (
            count($missingFields) > 0
            || count($unexpectedFields) > 0
        ) {
            throw new RuntimeException(
                'Bộ lọc từ AI có trường bị thiếu hoặc không được hỗ trợ.'
            );
        }

        return $searchFilters;
    }

    // Kiểm tra kiểu dữ liệu và giới hạn các giá trị AI trả về.
    public function validateFilterValues(array $searchFilters): array
    {
        $textLimits = [
            'keyword' => 100,
            'province_code' => 10,
            'tag_slug' => 100,
        ];

        foreach ($textLimits as $fieldName => $maximumLength) {
            $fieldValue = $searchFilters[$fieldName];

            if ($fieldValue === null) {
                continue;
            }

            if (is_string($fieldValue) === false) {
                throw new RuntimeException(
                    'Bộ lọc ' . $fieldName . ' phải là chuỗi hoặc null.'
                );
            }

            $fieldValue = trim($fieldValue);

            if (mb_strlen($fieldValue, 'UTF-8') > $maximumLength) {
                throw new RuntimeException(
                    'Bộ lọc ' . $fieldName . ' vượt quá độ dài cho phép.'
                );
            }

            if ($fieldValue === '') {
                $fieldValue = null;
            }

            $searchFilters[$fieldName] = $fieldValue;
        }

        $categoryId = $searchFilters['category_id'];

        if ($categoryId !== null) {
            if (is_int($categoryId) === false || $categoryId <= 0) {
                throw new RuntimeException(
                    'Mã danh mục phải là số nguyên dương hoặc null.'
                );
            }
        }

        $unsupportedCriteria = $searchFilters['unsupported'];

        if (
            is_array($unsupportedCriteria) === false
            || array_is_list($unsupportedCriteria) === false
            || count($unsupportedCriteria) > 3
        ) {
            throw new RuntimeException(
                'Danh sách tiêu chí chưa hỗ trợ không hợp lệ.'
            );
        }

        foreach ($unsupportedCriteria as $criterionIndex => $criterionText) {
            if (is_string($criterionText) === false) {
                throw new RuntimeException(
                    'Tiêu chí chưa hỗ trợ phải là chuỗi.'
                );
            }

            $criterionText = trim($criterionText);

            if (
                $criterionText === ''
                || mb_strlen($criterionText, 'UTF-8') > 100
            ) {
                throw new RuntimeException(
                    'Tiêu chí chưa hỗ trợ phải có từ 1 đến 100 ký tự.'
                );
            }

            $unsupportedCriteria[$criterionIndex] = $criterionText;
        }

        $searchFilters['unsupported'] = $unsupportedCriteria;

        return $searchFilters;
    }

    // Chỉ chấp nhận mã và nhãn có trong danh sách đã cung cấp cho AI.
    public function validateCatalogFilters(
        array $searchFilters,
        array $searchCatalog
    ): array {
        $allowedCategoryIds = [];

        foreach ($searchCatalog['categories'] as $category) {
            $allowedCategoryIds[] = (int) $category['category_id'];
        }

        $allowedProvinceCodes = [];

        foreach ($searchCatalog['provinces'] as $province) {
            $allowedProvinceCodes[] = (string) $province['province_code'];
        }

        $allowedTagSlugs = [];

        foreach ($searchCatalog['tags'] as $tag) {
            $allowedTagSlugs[] = (string) $tag['slug'];
        }

        if ($searchFilters['category_id'] !== null) {
            if (
                in_array(
                    $searchFilters['category_id'],
                    $allowedCategoryIds,
                    true
                ) === false
            ) {
                throw new RuntimeException(
                    'AI trả về danh mục không có trong danh sách.'
                );
            }
        }

        if ($searchFilters['province_code'] !== null) {
            if (
                in_array(
                    $searchFilters['province_code'],
                    $allowedProvinceCodes,
                    true
                ) === false
            ) {
                throw new RuntimeException(
                    'AI trả về tỉnh/thành không có trong danh sách.'
                );
            }
        }

        if ($searchFilters['tag_slug'] !== null) {
            if (
                in_array(
                    $searchFilters['tag_slug'],
                    $allowedTagSlugs,
                    true
                ) === false
            ) {
                throw new RuntimeException(
                    'AI trả về nhãn không có trong danh sách.'
                );
            }
        }

        return $searchFilters;
    }

    // Tìm tối đa 6 địa điểm công khai bằng nguồn tìm kiếm của Thuần.
    public function findMatchingPlaces(array $searchFilters): array
    {
        $searchFilters = $this->validateFilterValues($searchFilters);

        $currentCatalog = $this->getSearchCatalog();
        $searchFilters = $this->validateCatalogFilters(
            $searchFilters,
            $currentCatalog
        );

        $hasSearchCondition = false;

        $searchFields = [
            'keyword',
            'category_id',
            'province_code',
            'tag_slug',
        ];

        foreach ($searchFields as $fieldName) {
            if ($searchFilters[$fieldName] !== null) {
                $hasSearchCondition = true;
                break;
            }
        }

        if ($hasSearchCondition === false) {
            return [
                'places' => [],
                'total_count' => 0,
                'needs_clarification' => true,
            ];
        }

        $placeSearchModel = new PlaceSearch();

        $searchResult = $placeSearchModel->searchApprovedForHome(
            $searchFilters['keyword'],
            $searchFilters['category_id'],
            $searchFilters['province_code'],
            null,
            $searchFilters['tag_slug'],
            1,
            6
        );

        $searchResult['needs_clarification'] = false;

        return $searchResult;
    }

    // Phân tích nhu cầu và chỉ trả về bộ lọc đã được kiểm tra.
    public function interpretRequest(string $requestText): array
    {
        $requestText = $this->validateRequest($requestText);
        $searchCatalog = $this->getSearchCatalog();

        $claudeClient = new ClaudeClient();

        $responseText = $claudeClient->extractPlaceSearchFilters(
            $requestText,
            $searchCatalog
        );

        $searchFilters = $this->decodeSearchFilters($responseText);
        $searchFilters = $this->validateFilterValues($searchFilters);

        return $this->validateCatalogFilters(
            $searchFilters,
            $searchCatalog
        );
    }

    // Đọc bộ lọc đã lưu cho cùng người dùng và nội dung yêu cầu.
    public function readCachedFilters(
        string $requestText,
        int $userId
    ): ?array {
        $cacheKey = $this->createCacheKey($requestText, $userId);
        $filterCache = new AiSummaryCache();

        $cachedText = $filterCache->read($cacheKey);

        if ($cachedText === null) {
            return null;
        }

        $searchFilters = $this->decodeSearchFilters($cachedText);

        return $this->validateFilterValues($searchFilters);
    }

    // Lưu bộ lọc đã kiểm tra để tái sử dụng trong một giờ.
    public function cacheFilters(
        string $requestText,
        int $userId,
        array $searchFilters
    ): bool {
        $cacheKey = $this->createCacheKey($requestText, $userId);

        $filterText = json_encode(
            $searchFilters,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $filterCache = new AiSummaryCache();

        return $filterCache->write($cacheKey, $filterText);
    }

    // Tách cache gợi ý địa điểm khỏi các chức năng AI khác.
    private function createCacheKey(
        string $requestText,
        int $userId
    ): string {
        return 'place-recommendation-v1:'
            . $userId
            . ':'
            . $requestText;
    }
}
