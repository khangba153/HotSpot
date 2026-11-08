<?php

declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\Place;
use HotSpot\Models\User;
use Throwable;
use HotSpot\Core\ClaudeClient;
use HotSpot\Models\Review;
use HotSpot\Core\AiSummaryCache;
use HotSpot\Services\AiPlaceRecommendation;
use InvalidArgumentException;

final class AiController extends Controller
{
    private const REQUEST_INTERVAL_SECONDS = 10;

    // Kiểm tra người dùng và địa điểm trước khi xử lý tóm tắt.
    public function summarizeReviews(): void
    {
        $userId = $this->validateAiSession();

        if ($userId === null) {
            return;
        }

        $submittedPlaceId = $_POST['place_id'] ?? '';

        if (
            is_string($submittedPlaceId) === false
            || strlen($submittedPlaceId) > 18
            || ctype_digit($submittedPlaceId) === false
            || (int) $submittedPlaceId <= 0
        ) {
            $this->respond(422, false, 'Địa điểm không hợp lệ.');
            return;
        }

        $placeId = (int) $submittedPlaceId;

        try {
            $userModel = new User();
            $userInformation = $userModel->findById($userId);

            if ($userInformation === null) {
                $this->respond(401, false, 'Vui lòng đăng nhập lại.');
                return;
            }

            $placeModel = new Place();
            $placeInformation = $placeModel->findDetailById($placeId);

            if (
                $placeInformation === null
                || $placeInformation['status_code'] !== 'APPROVED'
            ) {
                $this->respond(
                    404,
                    false,
                    'Không tìm thấy địa điểm đang công khai.'
                );
                return;
            }

            $reviewModel = new Review();
            $reviewList = $reviewModel->findVisibleByPlaceId($placeId);

            if ($reviewList === []) {
                $this->respond(
                    422,
                    false,
                    'Địa điểm chưa có đánh giá công khai để tóm tắt.'
                );
                return;
            }

            $reviewList = array_slice($reviewList, 0, 10);

            $reviewCacheData = [];

            foreach ($reviewList as $reviewInformation) {
                $reviewCacheData[] = [
                    'review_id' => (int) $reviewInformation['review_id'],
                    'rating' => (int) $reviewInformation['rating'],
                    'content' => mb_substr(
                        trim((string) $reviewInformation['content']),
                        0,
                        300,
                        'UTF-8'
                    ),
                ];
            }

            // Khóa thay đổi khi địa điểm hoặc dữ liệu đánh giá thay đổi.
            $cacheKey = 'review-summary-v1:'
                . $placeId
                . ':'
                . json_encode(
                    $reviewCacheData,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                );

            $summaryCache = new AiSummaryCache();
            $cachedSummary = $summaryCache->read($cacheKey);

            if ($cachedSummary !== null) {
                $this->respond(
                    200,
                    true,
                    'Đã lấy bản tóm tắt được lưu trước đó.',
                    [
                        'summary' => $cachedSummary,
                        'cached' => true,
                    ]
                );
                return;
            }

            $currentTime = time();
            $lastRequestTime = $_SESSION['ai_requested_at'] ?? 0;

            if ($currentTime - $lastRequestTime < self::REQUEST_INTERVAL_SECONDS) {
                $this->respond(
                    429,
                    false,
                    'Vui lòng chờ 10 giây giữa các lần yêu cầu AI.'
                );
                return;
            }

            // Ghi thời điểm trước khi gọi để hạn chế yêu cầu liên tiếp.
            $_SESSION['ai_requested_at'] = $currentTime;

            $claudeClient = new ClaudeClient();
            $summary = $claudeClient->summarizeReviews($reviewList);

            $cacheSaved = $summaryCache->write($cacheKey, $summary);

            if ($cacheSaved === false) {
                error_log('AI summary cache could not be saved.');
            }

            $this->respond(
                200,
                true,
                'Đã tóm tắt đánh giá.',
                [
                    'summary' => $summary,
                    'cached' => false,
                ]
            );
        } catch (Throwable $exception) {
            error_log('AI summary error: ' . get_class($exception));

            $this->respond(
                500,
                false,
                'Chưa thể xử lý yêu cầu. Vui lòng thử lại sau.'
            );
        }
    }

    // Nhận tên địa điểm và thông tin thực tế để tạo bản nháp.
    public function draftPlaceDescription(): void
    {
        $userId = $this->validateAiSession();

        if ($userId === null) {
            return;
        }

        $placeTitle = $_POST['title'] ?? '';
        $facts = $_POST['facts'] ?? '';

        if (
            is_string($placeTitle) === false
            || is_string($facts) === false
        ) {
            $this->respond(422, false, 'Dữ liệu giới thiệu không hợp lệ.');
            return;
        }

        $placeTitle = trim($placeTitle);
        $facts = trim($facts);

        if ($placeTitle === '' || $facts === '') {
            $this->respond(
                422,
                false,
                'Vui lòng nhập tên địa điểm và các thông tin thực tế.'
            );
            return;
        }

        if (
            mb_strlen($placeTitle, 'UTF-8') > 100
            || mb_strlen($facts, 'UTF-8') > 1000
        ) {
            $this->respond(
                422,
                false,
                'Tên tối đa 100 ký tự, thông tin tối đa 1.000 ký tự.'
            );
            return;
        }

        try {
            $userModel = new User();
            $userInformation = $userModel->findById($userId);

            if ($userInformation === null) {
                $this->respond(401, false, 'Vui lòng đăng nhập lại.');
                return;
            }

            $cacheKey = 'place-description-v1:'
                . $userId
                . ':'
                . json_encode([
                    'title' => $placeTitle,
                    'facts' => $facts,
                ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            $descriptionCache = new AiSummaryCache();
            $cachedDescription = $descriptionCache->read($cacheKey);

            if ($cachedDescription !== null) {
                $this->respond(
                    200,
                    true,
                    'Đã lấy bản nháp được lưu trước đó.',
                    [
                        'description' => $cachedDescription,
                        'cached' => true,
                    ]
                );
                return;
            }

            $currentTime = time();
            $lastRequestTime = $_SESSION['ai_requested_at'] ?? 0;

            if ($currentTime - $lastRequestTime < self::REQUEST_INTERVAL_SECONDS) {
                $this->respond(
                    429,
                    false,
                    'Vui lòng chờ 10 giây giữa các lần yêu cầu AI.'
                );
                return;
            }

            $_SESSION['ai_requested_at'] = $currentTime;

            $claudeClient = new ClaudeClient();

            $description = $claudeClient->draftPlaceDescription(
                $placeTitle,
                $facts
            );

            $cacheSaved = $descriptionCache->write(
                $cacheKey,
                $description
            );

            if ($cacheSaved === false) {
                error_log('AI description cache could not be saved.');
            }

            $this->respond(
                200,
                true,
                'Đã tạo bản nháp giới thiệu.',
                [
                    'description' => $description,
                    'cached' => false,
                ]
            );
        } catch (Throwable $exception) {
            error_log('AI description error: ' . get_class($exception));

            $this->respond(
                500,
                false,
                'Chưa thể tạo bản nháp. Vui lòng thử lại sau.'
            );
        }
    }

    // Nhận nhu cầu và trả về các địa điểm công khai phù hợp.
    public function recommendPlaces(): void
    {
        $userId = $this->validateAiSession();

        if ($userId === null) {
            return;
        }

        $requestText = $_POST['request_text'] ?? '';

        if (is_string($requestText) === false) {
            $this->respond(422, false, 'Nội dung tìm kiếm không hợp lệ.');
            return;
        }

        $recommendationService = new AiPlaceRecommendation();

        try {
            $requestText = $recommendationService->validateRequest(
                $requestText
            );
        } catch (InvalidArgumentException $exception) {
            $this->respond(422, false, $exception->getMessage());
            return;
        }

        try {
            $userModel = new User();
            $userInformation = $userModel->findById($userId);

            if ($userInformation === null) {
                $this->respond(401, false, 'Vui lòng đăng nhập lại.');
                return;
            }

            $searchFilters = $recommendationService->readCachedFilters(
                $requestText,
                $userId
            );

            $isCached = $searchFilters !== null;

            if ($searchFilters === null) {
                $currentTime = time();
                $lastRequestTime = $_SESSION['ai_requested_at'] ?? 0;

                if ($currentTime - $lastRequestTime < self::REQUEST_INTERVAL_SECONDS) {
                    $this->respond(
                        429,
                        false,
                        'Vui lòng chờ 10 giây giữa các lần yêu cầu AI.'
                    );
                    return;
                }

                $_SESSION['ai_requested_at'] = $currentTime;

                $searchFilters = $recommendationService->interpretRequest(
                    $requestText
                );

                $cacheSaved = $recommendationService->cacheFilters(
                    $requestText,
                    $userId,
                    $searchFilters
                );

                if ($cacheSaved === false) {
                    error_log('AI recommendation cache could not be saved.');
                }
            }

            $searchResult = $recommendationService->findMatchingPlaces(
                $searchFilters
            );

            $message = 'Đã tìm địa điểm theo các bộ lọc xác định được.';

            if ($searchResult['needs_clarification']) {
                $message = 'Vui lòng nêu rõ tên địa điểm, danh mục, '
                    . 'tỉnh/thành, nhãn hoặc tiêu chí xếp hạng bạn muốn tìm.';
            } elseif ($searchResult['total_count'] === 0) {
                $message = 'Chưa tìm thấy địa điểm phù hợp trong dữ liệu.';
            }

            $this->respond(
                200,
                true,
                $message,
                [
                    'filters' => $searchFilters,
                    'places' => $searchResult['places'],
                    'total_count' => $searchResult['total_count'],
                    'needs_clarification' =>
                    $searchResult['needs_clarification'],
                    'cached' => $isCached,
                ]
            );
        } catch (Throwable $exception) {
            error_log(
                'AI recommendation error: ' . get_class($exception)
            );

            $this->respond(
                500,
                false,
                'Chưa thể gợi ý địa điểm. Vui lòng thử lại sau.'
            );
        }
    }

    // Kiểm tra phiên đăng nhập và CSRF, trả về ID người dùng.
    private function validateAiSession(): ?int
    {
        $userId = filter_var(
            $_SESSION['user_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (is_int($userId) === false || $userId <= 0) {
            $this->respond(
                401,
                false,
                'Vui lòng đăng nhập để sử dụng AI.'
            );
            return null;
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (
            is_string($submittedToken) === false
            || is_string($sessionToken) === false
            || $sessionToken === ''
            || hash_equals($sessionToken, $submittedToken) === false
        ) {
            $this->respond(
                403,
                false,
                'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.'
            );
            return null;
        }

        return $userId;
    }

    // Trả kết quả JSON thống nhất cho JavaScript.
    private function respond(
        int $httpStatusCode,
        bool $success,
        string $message,
        ?array $data = null
    ): void {
        http_response_code($httpStatusCode);

        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
