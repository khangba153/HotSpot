<?php

declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\Favorite;
use HotSpot\Models\User;
use HotSpot\Core\HttpException;
use PDOException;
use Throwable;

final class FavoriteController extends Controller
{

    // Hiển thị danh sách yêu thích của tài khoản đang đăng nhập.
    public function index(): void
    {
        if (isset($_SESSION['user_id']) === false) {
            throw new HttpException(
                401,
                'Vui lòng đăng nhập để xem danh sách yêu thích.'
            );
        }

        $userId = (int) $_SESSION['user_id'];

        $userModel = new User();
        $userInformation = $userModel->findById($userId);

        if ($userInformation === null) {
            throw new HttpException(
                401,
                'Tài khoản không tồn tại. Vui lòng đăng nhập lại.'
            );
        }

        if (isset($_SESSION['csrf_token']) === false) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $favoriteModel = new Favorite();
        $favoritePlaces = $favoriteModel->findApprovedByUserId($userId);

        foreach ($favoritePlaces as $placeIndex => $placeInformation) {
            $imagePath = trim((string) $placeInformation['cover_image']);

            $favoritePlaces[$placeIndex]['cover_image'] =
                $this->getCoverImageUrl($imagePath);
        }

        $viewData = [
            'title' => 'Địa điểm yêu thích',
            'userInformation' => $userInformation,
            'favoritePlaces' => $favoritePlaces,
            'csrfToken' => $_SESSION['csrf_token'],
            'isFavoritesPage' => true,
        ];

        $this->render('favorites/index', $viewData);
    }
    // Kiểm tra request và cập nhật yêu thích của tài khoản trong session.
    public function set(): void
    {
        if (isset($_SESSION['user_id']) === false) {
            $this->sendJson(401, false, 'Vui lòng đăng nhập.');
            return;
        }

        try {
            $userId = (int) $_SESSION['user_id'];

            $userModel = new User();
            $userInformation = $userModel->findById($userId);

            if ($userInformation === null) {
                $this->sendJson(401, false, 'Tài khoản không tồn tại.');
                return;
            }

            $allowedFields = [
                'place_id',
                'desired_state',
                'csrf_token',
            ];

            foreach ($_POST as $fieldName => $fieldValue) {
                if (in_array($fieldName, $allowedFields, true) === false) {
                    $this->sendJson(
                        400,
                        false,
                        'Request chứa trường không được phép.'
                    );
                    return;
                }
            }

            $submittedToken = '';

            if (
                isset($_POST['csrf_token'])
                && is_string($_POST['csrf_token'])
            ) {
                $submittedToken = $_POST['csrf_token'];
            }

            if (isset($_SESSION['csrf_token']) === false) {
                $this->sendJson(
                    403,
                    false,
                    'Phiên làm việc không hợp lệ.'
                );
                return;
            }

            if (
                hash_equals(
                    $_SESSION['csrf_token'],
                    $submittedToken
                ) === false
            ) {
                $this->sendJson(
                    403,
                    false,
                    'Form không hợp lệ. Vui lòng tải lại trang.'
                );
                return;
            }

            if (
                isset($_POST['place_id']) === false
                || is_string($_POST['place_id']) === false
            ) {
                $this->sendJson(400, false, 'Địa điểm không hợp lệ.');
                return;
            }

            $placeId = filter_var(
                $_POST['place_id'],
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($placeId === false) {
                $this->sendJson(400, false, 'Địa điểm không hợp lệ.');
                return;
            }

            if (
                isset($_POST['desired_state']) === false
                || is_string($_POST['desired_state']) === false
            ) {
                $this->sendJson(
                    400,
                    false,
                    'Trạng thái yêu thích không hợp lệ.'
                );
                return;
            }

            $desiredState = $_POST['desired_state'];

            if ($desiredState !== '0' && $desiredState !== '1') {
                $this->sendJson(
                    400,
                    false,
                    'Trạng thái yêu thích chỉ nhận 0 hoặc 1.'
                );
                return;
            }

            $favoriteModel = new Favorite();

            $isFavorited = $favoriteModel->setState(
                $userId,
                $placeId,
                (int) $desiredState
            );

            $this->sendJson(
                200,
                true,
                'Đã cập nhật trạng thái yêu thích.',
                [
                    'place_id' => $placeId,
                    'is_favorited' => $isFavorited,
                ]
            );
        } catch (PDOException $exception) {
            // Routine dùng SQLSTATE 45000 khi từ chối nghiệp vụ.
            if ((string) $exception->getCode() === '45000') {
                $this->sendJson(
                    409,
                    false,
                    'Không thể cập nhật. Địa điểm chưa công khai hoặc dữ liệu đã thay đổi.'
                );
                return;
            }

            $this->sendJson(
                500,
                false,
                'Chưa thể cập nhật yêu thích. Vui lòng thử lại.'
            );
        } catch (Throwable $exception) {
            $this->sendJson(
                500,
                false,
                'Chưa thể cập nhật yêu thích. Vui lòng thử lại.'
            );
        }
    }

    // Chọn ảnh hợp lệ; dùng ảnh minh họa khi đường dẫn thiếu hoặc không an toàn.
    private function getCoverImageUrl(string $imagePath): string
    {
        $fallbackImageUrl = 'assets/images/home/travel.webp';

        if (
            $imagePath === ''
            || $imagePath === 'assets/images/demo-placeholder.svg'
        ) {
            return $fallbackImageUrl;
        }

        $isLocalPath = preg_match(
            '#^(assets|uploads)/[A-Za-z0-9._/-]+$#',
            $imagePath
        ) === 1;

        if ($isLocalPath && strpos($imagePath, '..') === false) {
            $imageFilePath = dirname(__DIR__, 2) . '/public/' . $imagePath;

            if (is_file($imageFilePath)) {
                return $imagePath;
            }
        }

        $isRemoteUrl = preg_match('#^https?://#i', $imagePath) === 1;

        if (
            $isRemoteUrl
            && filter_var($imagePath, FILTER_VALIDATE_URL) !== false
        ) {
            return $imagePath;
        }

        return $fallbackImageUrl;
    }

    // Trả JSON theo cấu trúc thống nhất cho JavaScript.
    private function sendJson(
        int $statusCode,
        bool $success,
        string $message,
        array $data = []
    ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(
            [
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'errors' => [],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
