<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\Place;
use HotSpot\Models\Review;
use HotSpot\Models\User;
use PDOException;
use Throwable;

final class ReviewController extends Controller
{
    public function save(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');

        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($userId) || $userId <= 0) {
            $this->respond(401, false, 'Vui lòng đăng nhập để gửi đánh giá.', null, []);
            return;
        }

        try {
            $user = (new User())->findById($userId);
            if ($user === null || ($user['role_code'] ?? '') !== 'MEMBER') {
                $this->respond(403, false, 'Chỉ thành viên mới có thể gửi đánh giá.', null, []);
                return;
            }

            $allowedFields = ['csrf_token', 'place_id', 'rating', 'content'];
            foreach ($_POST as $field => $_value) {
                if (!in_array($field, $allowedFields, true)) {
                    $this->respond(400, false, 'Dữ liệu gửi lên không hợp lệ.', null, []);
                    return;
                }
            }

            $submittedToken = $_POST['csrf_token'] ?? '';
            if (
                !is_string($submittedToken)
                || !isset($_SESSION['csrf_token'])
                || !hash_equals((string) $_SESSION['csrf_token'], $submittedToken)
            ) {
                $this->respond(400, false, 'Phiên làm việc hết hạn. Hãy tải lại trang và thử lại.', null, [
                    'csrf_token' => 'Mã bảo vệ không hợp lệ.',
                ]);
                return;
            }

            $rawPlaceId = $_POST['place_id'] ?? '';
            $rawRating = $_POST['rating'] ?? '';
            $rawContent = $_POST['content'] ?? '';
            $errors = [];

            if (
                !is_string($rawPlaceId)
                || strlen($rawPlaceId) > 18
                || !ctype_digit($rawPlaceId)
                || (int) $rawPlaceId <= 0
            ) {
                $errors['place_id'] = 'Địa điểm không hợp lệ.';
            }
            if (!is_string($rawRating) || !in_array($rawRating, ['1', '2', '3', '4', '5'], true)) {
                $errors['rating'] = 'Vui lòng chọn từ 1 đến 5 sao.';
            }
            if (!is_string($rawContent)) {
                $errors['content'] = 'Nội dung đánh giá không hợp lệ.';
                $content = '';
            } else {
                $content = trim($rawContent);
                if ($content === '' || preg_match('/[^\s]/u', $content) !== 1) {
                    $errors['content'] = 'Vui lòng nhập nội dung đánh giá.';
                } elseif ($this->characterLength($content) > 2000) {
                    $errors['content'] = 'Nội dung tối đa 2.000 ký tự.';
                }
            }

            if ($errors !== []) {
                $this->respond(422, false, 'Vui lòng kiểm tra lại đánh giá.', null, $errors);
                return;
            }

            $placeId = (int) $rawPlaceId;
            $place = (new Place())->findDetailById($placeId);
            if ($place === null || ($place['status_code'] ?? '') !== 'APPROVED') {
                $this->respond(422, false, 'Chỉ có thể đánh giá địa điểm đang công khai.', null, [
                    'place_id' => 'Địa điểm chưa được duyệt hoặc không còn công khai.',
                ]);
                return;
            }

            // ID người gửi luôn lấy từ session, không tin giá trị user gửi lên.
            $savedReview = (new Review())->saveOrUpdate($userId, $placeId, (int) $rawRating, $content);
            $this->respond(200, true, 'Đánh giá của bạn đã được lưu.', [
                'review' => $savedReview,
                'visible' => $savedReview['status_code'] === 'VISIBLE',
            ], []);
        } catch (Throwable $exception) {
            error_log('Review save error: ' . $exception->getMessage());
            if ($exception instanceof PDOException && (string) $exception->getCode() === '45000') {
                $this->respond(422, false, 'Đánh giá không hợp lệ hoặc địa điểm không còn công khai.', null, []);
                return;
            }

            $this->respond(500, false, 'Hệ thống chưa thể lưu đánh giá lúc này.', null, []);
        }
    }

    private function characterLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
        return is_array($characters) ? count($characters) : strlen($value);
    }

    private function respond(int $status, bool $success, string $message, ?array $data, array $errors): void
    {
        http_response_code($status);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
