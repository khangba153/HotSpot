<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\PlaceModeration;
use HotSpot\Models\AdminCategory;
use HotSpot\Models\Review;
use HotSpot\Models\User;

final class AdminController extends Controller
{
    // Chỉ Admin được xem số liệu và lối tắt quản trị.
    public function dashboard(): void
    {
        $admin = $this->requireAdmin();
        $moderation = new PlaceModeration();
        $this->render('admin/index', [
            'title' => 'Tổng quan quản trị', 'userInformation' => $admin,
            'counts' => $moderation->findDashboardCounts(),
            'pendingPlaces' => $moderation->findRecentPendingPlaces(),
            'isAdminPage' => true,
        ]);
    }

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
        $showManagedPlaces = isset($_GET['view']) && $_GET['view'] === 'managed';
        $placeModeration = new PlaceModeration();
        $pendingPlaces = [];
        $managedPlaces = [];
        $pageTitle = 'Địa điểm chờ duyệt';
        if ($showManagedPlaces) {
            $managedPlaces = $placeModeration->findManagedPlaces();
            $pageTitle = 'Quản lý địa điểm';
        } else {
            $pendingPlaces = $placeModeration->findPendingPlaces();
        }
        $this->render('admin/places', [
            'title' => $pageTitle,
            'userInformation' => $user,
            'pendingPlaces' => $pendingPlaces,
            'managedPlaces' => $managedPlaces,
            'showManagedPlaces' => $showManagedPlaces,
            'csrfToken' => $_SESSION['csrf_token'] ?? '',
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
        $allowedFields = ['csrf_token', 'place_id', 'target_status', 'rejection_reason', 'moderation_action'];
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
        $moderationAction = $_POST['moderation_action'] ?? 'review';
        if (!is_string($moderationAction) || !in_array($moderationAction, ['review', 'hide', 'restore'], true)) {
            throw new HttpException(400, 'Thao tác không hợp lệ.');
        }
        $expectedStatus = 'PENDING';
        if ($moderationAction === 'hide') {
            $expectedStatus = 'APPROVED';
        } elseif ($moderationAction === 'restore') {
            $expectedStatus = 'HIDDEN';
        }
        $validTarget = ($moderationAction === 'review' && in_array($targetStatus, ['APPROVED', 'REJECTED'], true))
            || ($moderationAction === 'hide' && $targetStatus === 'HIDDEN')
            || ($moderationAction === 'restore' && $targetStatus === 'APPROVED');
        if (!is_string($targetStatus) || !$validTarget) {
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
            (new PlaceModeration())->setPlaceStatus(
                (int) $admin['user_id'],
                (int) $rawPlaceId,
                $targetStatus,
                $reason === '' ? null : $reason,
                $expectedStatus
            );
        } catch (\PDOException $exception) {
            if ($moderationAction !== 'review') {
                $this->redirectToManagedPlaces('Không thể đổi trạng thái. Hãy kiểm tra dữ liệu địa điểm và routine; khi khôi phục cần có từ 1 đến 3 ảnh.', 'danger');
            }
            $this->redirectWithAdminMessage((int) $rawPlaceId, 'Không thể lưu quyết định. Hãy kiểm tra cấu hình cơ sở dữ liệu và routine.', 'danger');
        } catch (\RuntimeException $exception) {
            if ($moderationAction !== 'review') {
                $this->redirectToManagedPlaces($exception->getMessage(), 'danger');
            }
            $this->redirectWithAdminMessage((int) $rawPlaceId, $exception->getMessage(), 'danger');
        } catch (\Throwable $exception) {
            if ($moderationAction !== 'review') {
                $this->redirectToManagedPlaces('Chưa thể đổi trạng thái. Vui lòng thử lại.', 'danger');
            }
            $this->redirectWithAdminMessage((int) $rawPlaceId, 'Chưa thể lưu quyết định. Vui lòng thử lại.', 'danger');
        }

        if ($moderationAction === 'hide') {
            $this->redirectToManagedPlaces('Đã ẩn địa điểm.', 'success');
        }
        if ($moderationAction === 'restore') {
            $this->redirectToManagedPlaces('Đã khôi phục địa điểm.', 'success');
        }

        $_SESSION['admin_message'] = $targetStatus === 'APPROVED'
            ? 'Đã duyệt địa điểm.'
            : 'Đã từ chối địa điểm và lưu lý do.';
        $_SESSION['admin_message_type'] = 'success';
        header('Location: ' . createRouteUrl('admin/places'));
        exit;
    }

    // Hiển thị danh sách đánh giá cho Admin.
    public function reviews(): void
    {
        $admin = $this->requireAdmin();
        $reviews = (new Review())->findAllForAdmin();
        $message = $_SESSION['review_admin_message'] ?? '';
        $messageType = $_SESSION['review_admin_message_type'] ?? 'success';
        unset($_SESSION['review_admin_message'], $_SESSION['review_admin_message_type']);
        $this->render('admin/reviews', [
            'title' => 'Quản lý đánh giá', 'userInformation' => $admin,
            'reviews' => $reviews, 'csrfToken' => $_SESSION['csrf_token'] ?? '',
            'message' => $message, 'messageType' => $messageType, 'isAdminPage' => true,
        ]);
    }

    // Chỉ Admin có CSRF hợp lệ mới được ẩn hoặc khôi phục đánh giá.
    public function updateReviewStatus(): void
    {
        $admin = $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new HttpException(400, 'Phiên làm việc hết hạn. Hãy tải lại trang và thử lại.');
        }
        $reviewId = $_POST['review_id'] ?? '';
        $targetStatus = $_POST['target_status'] ?? '';
        if (!is_string($reviewId) || strlen($reviewId) > 18 || !ctype_digit($reviewId) || (int) $reviewId <= 0
            || !is_string($targetStatus) || !in_array($targetStatus, ['VISIBLE', 'HIDDEN'], true)) {
            throw new HttpException(400, 'Thông tin đánh giá không hợp lệ.');
        }
        try {
            (new Review())->setModerationStatus((int) $admin['user_id'], (int) $reviewId, $targetStatus);
            $_SESSION['review_admin_message'] = $targetStatus === 'HIDDEN' ? 'Đã ẩn đánh giá.' : 'Đã khôi phục đánh giá.';
            $_SESSION['review_admin_message_type'] = 'success';
        } catch (\PDOException $exception) {
            $_SESSION['review_admin_message'] = 'Không thể cập nhật đánh giá. Vui lòng thử lại.';
            $_SESSION['review_admin_message_type'] = 'danger';
        } catch (\RuntimeException $exception) {
            $_SESSION['review_admin_message'] = $exception->getMessage();
            $_SESSION['review_admin_message_type'] = 'danger';
        } catch (\Throwable $exception) {
            $_SESSION['review_admin_message'] = 'Không thể cập nhật đánh giá. Vui lòng thử lại.';
            $_SESSION['review_admin_message_type'] = 'danger';
        }
        header('Location: ' . createRouteUrl('admin/reviews'));
        exit;
    }

    // Danh sách và thống kê danh mục dành cho Admin.
    public function categories(): void
    {
        $admin = $this->requireAdmin();
        $message = $_SESSION['category_message'] ?? '';
        $messageType = $_SESSION['category_message_type'] ?? 'success';
        unset($_SESSION['category_message'], $_SESSION['category_message_type']);
        $this->render('admin/categories', [
            'title' => 'Quản lý danh mục', 'userInformation' => $admin,
            'categories' => (new AdminCategory())->findAll(),
            'csrfToken' => $_SESSION['csrf_token'] ?? '', 'isAdminPage' => true,
            'message' => $message, 'messageType' => $messageType,
        ]);
    }

    public function storeCategory(): void { $this->saveCategory(false); }
    public function updateCategory(): void { $this->saveCategory(true); }

    // Kiểm tra quyền, CSRF và dữ liệu trước khi ghi.
    private function saveCategory(bool $editing): void
    {
        $this->requireAdmin();
        $this->checkCategoryToken();
        $id = $editing ? $this->categoryId() : 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        if (!is_string($name) || !is_string($description)) {
            throw new HttpException(400, 'Dữ liệu danh mục không hợp lệ.');
        }
        $name = trim($name);
        $description = trim($description);
        if ($name === '' || mb_strlen($name, 'UTF-8') > 100 || mb_strlen($description, 'UTF-8') > 300) {
            $this->redirectCategory('Tên bắt buộc, tối đa 100 ký tự; mô tả tối đa 300 ký tự.', 'danger');
        }
        try {
            $model = new AdminCategory();
            if ($model->nameExists($name, $id)) {
                $this->redirectCategory('Tên danh mục đã tồn tại.', 'danger');
            }
            if ($editing) {
                if (!$model->update($id, $name, $description === '' ? null : $description)) {
                    $this->redirectCategory('Không tìm thấy danh mục.', 'danger');
                }
            } else {
                $model->create($name, $description === '' ? null : $description);
            }
        } catch (\PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                $this->redirectCategory('Tên danh mục đã tồn tại.', 'danger');
            }
            $this->redirectCategory('Không thể lưu danh mục. Vui lòng thử lại.', 'danger');
        }
        $this->redirectCategory($editing ? 'Đã cập nhật danh mục.' : 'Đã thêm danh mục.', 'success');
    }

    public function updateCategoryStatus(): void
    {
        $this->requireAdmin();
        $this->checkCategoryToken();
        $id = $this->categoryId();
        $active = $_POST['is_active'] ?? '';
        if (!is_string($active) || !in_array($active, ['0', '1'], true)) {
            throw new HttpException(400, 'Trạng thái không hợp lệ.');
        }
        try {
            if (!(new AdminCategory())->setStatus($id, (int) $active)) {
                $this->redirectCategory('Không tìm thấy danh mục.', 'danger');
            }
        } catch (\PDOException $exception) {
            $this->redirectCategory('Không thể đổi trạng thái danh mục.', 'danger');
        }
        $this->redirectCategory($active === '1' ? 'Đã kích hoạt danh mục.' : 'Đã ngừng sử dụng danh mục.', 'success');
    }

    private function checkCategoryToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new HttpException(400, 'Phiên làm việc hết hạn. Hãy tải lại trang.');
        }
    }

    private function categoryId(): int
    {
        $id = $_POST['category_id'] ?? '';
        if (!is_string($id) || strlen($id) > 10 || !ctype_digit($id) || (int) $id <= 0 || (int) $id > 4294967295) {
            throw new HttpException(400, 'Danh mục không hợp lệ.');
        }
        return (int) $id;
    }

    private function redirectCategory(string $message, string $type): void
    {
        $_SESSION['category_message'] = $message;
        $_SESSION['category_message_type'] = $type;
        header('Location: ' . createRouteUrl('admin/categories'));
        exit;
    }

    // Trở về trang quản lý sau khi ẩn hoặc khôi phục.
    private function redirectToManagedPlaces(string $message, string $type): void
    {
        $_SESSION['admin_message'] = $message;
        $_SESSION['admin_message_type'] = $type;
        header('Location: ' . createRouteUrl('admin/places') . '&view=managed');
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
