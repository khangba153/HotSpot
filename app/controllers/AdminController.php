<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\PlaceModeration;
use HotSpot\Models\User;

final class AdminController extends Controller
{
    public function places(): void
    {
        if (!isset($_SESSION['user_id'])) {
            throw new HttpException(401, 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = (new User())->findById((int) $_SESSION['user_id']);
        if ($user === null) {
            throw new HttpException(401, 'Tài khoản không tồn tại. Vui lòng đăng nhập lại.');
        }
        if (($user['role_code'] ?? '') !== 'ADMIN') {
            throw new HttpException(403, 'Bạn không có quyền truy cập trang quản trị.');
        }

        $adminMessage = $_SESSION['admin_message'] ?? '';
        $adminMessageType = $_SESSION['admin_message_type'] ?? 'success';
        unset($_SESSION['admin_message'], $_SESSION['admin_message_type']);
        $this->render('admin/places', [
            'title' => 'Địa điểm chờ duyệt',
            'userInformation' => $user,
            'pendingPlaces' => (new PlaceModeration())->findPendingPlaces(),
            'adminMessage' => $adminMessage,
            'adminMessageType' => $adminMessageType,
            'isAdminPage' => true,
        ]);
    }

    public function review(): void
    {
        $user = $this->requireAdmin();
        $rawId = $_GET['id'] ?? '';
        if (!is_string($rawId) || !ctype_digit($rawId) || (int) $rawId <= 0) {
            throw new HttpException(404, 'Khong tim thay dia diem.');
        }
        $place = (new PlaceModeration())->findPendingDetail((int) $rawId);
        if ($place === null) {
            throw new HttpException(404, 'Dia diem khong con cho duyet.');
        }
        $images = [];
        $fallbackImages = [
            1 => 'assets/images/home/food.webp',
            2 => 'assets/images/home/coffee.webp',
            3 => 'assets/images/home/travel.webp',
            4 => 'assets/images/home/fun.webp',
            5 => 'assets/images/home/nature.webp',
            6 => 'assets/images/home/culture.webp',
        ];
        $fallbackImage = $fallbackImages[(int) $place['category_id']] ?? 'assets/images/home/travel.webp';
        foreach ($place['images'] as $image) {
            $path = (string) $image['image_path'];
            if ($path === 'assets/images/demo-placeholder.svg') {
                $images[] = $fallbackImage;
            } elseif (preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $path) === 1 && strpos($path, '..') === false) {
                $images[] = $path;
            } elseif (filter_var($path, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $path) === 1) {
                $images[] = $path;
            }
        }
        $this->render('admin/place-review', [
            'title' => 'Xem xet dia diem', 'userInformation' => $user,
            'place' => $place, 'imageUrls' => $images,
            'csrfToken' => $_SESSION['csrf_token'] ?? '',
            'adminMessage' => $_SESSION['admin_message'] ?? '',
            'isAdminPage' => true,
        ]);
        unset($_SESSION['admin_message'], $_SESSION['admin_message_type']);
    }

    public function updatePlaceStatus(): void
    {
        $admin = $this->requireAdmin();
        $allowedFields = ['csrf_token', 'place_id', 'target_status', 'rejection_reason'];
        foreach ($_POST as $field => $value) {
            if (!in_array($field, $allowedFields, true)) {
                throw new HttpException(400, 'Form không hợp lệ.');
            }
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        if (!is_string($submittedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            throw new HttpException(400, 'Phiên làm việc hết hạn. Hãy tải lại trang và thử lại.');
        }

        $rawPlaceId = $_POST['place_id'] ?? '';
        $targetStatus = $_POST['target_status'] ?? '';
        if (!is_string($rawPlaceId) || strlen($rawPlaceId) > 18 || !ctype_digit($rawPlaceId) || (int) $rawPlaceId <= 0) {
            throw new HttpException(400, 'Địa điểm không hợp lệ.');
        }
        if (!is_string($targetStatus) || !in_array($targetStatus, ['APPROVED', 'REJECTED'], true)) {
            throw new HttpException(400, 'Trạng thái duyệt không hợp lệ.');
        }

        $reason = $_POST['rejection_reason'] ?? '';
        if (!is_string($reason)) {
            throw new HttpException(400, 'Lý do từ chối không hợp lệ.');
        }
        $reason = trim($reason);
        if ($targetStatus === 'REJECTED' && $reason === '') {
            $this->redirectWithAdminMessage((int) $rawPlaceId, 'Vui lòng nhập lý do từ chối.', 'danger');
        }
        if ($targetStatus === 'APPROVED') {
            $reason = '';
        }

        try {
            (new PlaceModeration())->setPendingPlaceStatus(
                (int) $admin['user_id'],
                (int) $rawPlaceId,
                $targetStatus,
                $reason === '' ? null : $reason
            );
        } catch (\PDOException $exception) {
            $this->redirectWithAdminMessage((int) $rawPlaceId, 'Không thể lưu quyết định. Hãy kiểm tra cấu hình cơ sở dữ liệu và routine.', 'danger');
        } catch (\RuntimeException $exception) {
            $this->redirectWithAdminMessage((int) $rawPlaceId, $exception->getMessage(), 'danger');
        } catch (\Throwable $exception) {
            $this->redirectWithAdminMessage((int) $rawPlaceId, 'Chưa thể lưu quyết định. Vui lòng thử lại.', 'danger');
        }

        $_SESSION['admin_message'] = $targetStatus === 'APPROVED'
            ? 'Đã duyệt địa điểm.'
            : 'Đã từ chối địa điểm và lưu lý do.';
        $_SESSION['admin_message_type'] = 'success';
        header('Location: ' . createRouteUrl('admin/places'));
        exit;
    }

    private function requireAdmin(): array
    {
        if (!isset($_SESSION['user_id'])) {
            throw new HttpException(401, 'Vui long dang nhap.');
        }
        $user = (new User())->findById((int) $_SESSION['user_id']);
        if ($user === null) {
            throw new HttpException(401, 'Tai khoan khong ton tai.');
        }
        if (($user['role_code'] ?? '') !== 'ADMIN') {
            throw new HttpException(403, 'Ban khong co quyen quan tri.');
        }
        return $user;
    }

    private function redirectWithAdminMessage(int $placeId, string $message, string $type): void
    {
        $_SESSION['admin_message'] = $message;
        $_SESSION['admin_message_type'] = $type;
        header('Location: ' . createRouteUrl('admin/places/review') . '&id=' . $placeId);
        exit;
    }
}
