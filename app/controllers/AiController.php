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

final class AiController extends Controller
{

    // Kiểm tra người dùng và địa điểm trước khi xử lý tóm tắt.
    public function summarizeReviews(): void
    {
        $userId = filter_var(
            $_SESSION['user_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (is_int($userId) === false || $userId <= 0) {
            $this->respond(401, false, 'Vui lòng đăng nhập để sử dụng AI.');
            return;
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
            $lastRequestTime = $_SESSION['ai_summary_requested_at'] ?? 0;

            if ($currentTime - $lastRequestTime < 60) {
                $this->respond(
                    429,
                    false,
                    'Vui lòng chờ 60 giây giữa các lần yêu cầu AI.'
                );
                return;
            }

            // Ghi thời điểm trước khi gọi để hạn chế yêu cầu liên tiếp.
            $_SESSION['ai_summary_requested_at'] = $currentTime;

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
