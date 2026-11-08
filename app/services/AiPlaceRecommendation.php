<?php

declare(strict_types=1);

namespace HotSpot\Services;

use InvalidArgumentException;
use HotSpot\Models\Category;
use HotSpot\Models\Province;
use HotSpot\Models\Tag;
use RuntimeException;
use HotSpot\Models\AiPlaceSearch;
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

        // Chỉ gỡ khối Markdown bao toàn bộ JSON, vẫn kiểm tra đủ schema bên dưới.
        if (preg_match('/\A```(?:json)?\s*\R(.*?)\R?```\z/is', $responseText, $matches) === 1) {
            $responseText = trim($matches[1]);
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
            'tag_slugs',
            'min_rating',
            'min_reviews',
            'min_favorites',
            'sort',
            'created_within_days',
            'has_coordinates',
            'limit',
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
        $searchFilters = $this->decodeSearchFilters(json_encode($searchFilters, JSON_THROW_ON_ERROR));
        $textLimits = [
            'keyword' => 100,
            'province_code' => 10,
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

        $tagSlugs = $searchFilters['tag_slugs'];
        if (!is_array($tagSlugs) || !array_is_list($tagSlugs) || count($tagSlugs) > 5) {
            throw new RuntimeException('Chỉ nhận tối đa 5 nhãn.');
        }
        $normalizedTagSlugs = [];
        foreach ($tagSlugs as $tagSlug) {
            if (!is_string($tagSlug) || trim($tagSlug) === '' || strlen($tagSlug) > 100) {
                throw new RuntimeException('Nhãn tìm kiếm không hợp lệ.');
            }
            $normalizedTagSlugs[] = trim($tagSlug);
        }
        $searchFilters['tag_slugs'] = array_values(array_unique($normalizedTagSlugs));

        $minimumRating = $searchFilters['min_rating'];
        if ($minimumRating !== null) {
            if ((!is_int($minimumRating) && !is_float($minimumRating))
                || !is_finite((float) $minimumRating) || $minimumRating < 1 || $minimumRating > 5) {
                throw new RuntimeException('Điểm tối thiểu phải từ 1 đến 5.');
            }
        }
        $integerLimits = ['min_reviews' => 10000, 'min_favorites' => 10000, 'created_within_days' => 3650];
        foreach ($integerLimits as $fieldName => $maximumValue) {
            $value = $searchFilters[$fieldName];
            if ($value !== null && (!is_int($value) || $value < 1 || $value > $maximumValue)) {
                throw new RuntimeException('Giá trị ' . $fieldName . ' không hợp lệ.');
            }
        }
        $allowedSorts = ['default', 'rating_desc', 'reviews_desc', 'favorites_desc', 'newest', 'oldest', 'updated_desc'];
        if (!is_string($searchFilters['sort']) || !in_array($searchFilters['sort'], $allowedSorts, true)) {
            throw new RuntimeException('Cách sắp xếp không hợp lệ.');
        }
        if (!is_bool($searchFilters['has_coordinates'])) {
            throw new RuntimeException('Điều kiện tọa độ phải là boolean.');
        }
        if (!is_int($searchFilters['limit']) || $searchFilters['limit'] < 1 || $searchFilters['limit'] > 12) {
            throw new RuntimeException('Chỉ hiển thị từ 1 đến 12 địa điểm.');
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

        foreach ($searchFilters['tag_slugs'] as $tagSlug) {
            if (!in_array($tagSlug, $allowedTagSlugs, true)) {
                throw new RuntimeException('AI trả về nhãn không có trong danh sách.');
            }
        }

        return $searchFilters;
    }

    // Tìm và sắp xếp trên dữ liệu công khai; không dùng SQL do AI tạo.
    public function findMatchingPlaces(array $searchFilters): array
    {
        $searchFilters = $this->validateFilterValues($searchFilters);
        $searchFilters = $this->validateCatalogFilters($searchFilters, $this->getSearchCatalog());
        $hasSearchCondition = false;
        foreach (['keyword', 'category_id', 'province_code', 'min_rating', 'min_reviews',
            'min_favorites', 'created_within_days'] as $fieldName) {
            if ($searchFilters[$fieldName] !== null) {
                $hasSearchCondition = true;
            }
        }
        if ($searchFilters['tag_slugs'] !== [] || $searchFilters['has_coordinates']
            || $searchFilters['sort'] !== 'default' || $searchFilters['limit'] !== 6) {
            $hasSearchCondition = true;
        }
        if (!$hasSearchCondition) {
            return ['places' => [], 'total_count' => 0, 'needs_clarification' => true];
        }
        $searchModel = new AiPlaceSearch();
        $result = $searchModel->search($searchFilters);
        $result['needs_clarification'] = false;
        return $result;
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

        // Cache có thể cũ khi Admin ngừng sử dụng danh mục hoặc nhãn.
        try {
            $searchFilters = $this->decodeSearchFilters($cachedText);
            $searchFilters = $this->validateFilterValues($searchFilters);
            return $this->validateCatalogFilters($searchFilters, $this->getSearchCatalog());
        } catch (RuntimeException $exception) {
            return null;
        }
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
        return 'place-recommendation-v2:'
            . $userId
            . ':'
            . $requestText;
    }
}
