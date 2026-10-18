<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\User;
use PDOException;

final class ProfileController extends Controller
{
    // Kiểm tra đăng nhập, lấy tài khoản hiện tại và hiển thị Profile.
    public function index(): void
    {
        if (isset($_SESSION['user_id']) === false) {
            throw new HttpException(401, 'Vui lòng đăng nhập để xem hồ sơ.');
        }

        $userId = (int) $_SESSION['user_id'];

        $userModel = new User();
        $userInformation = $userModel->findById($userId);

        if ($userInformation === null) {
            throw new HttpException(401, 'Tài khoản không tồn tại. Vui lòng đăng nhập lại.');
        }

        // Đọc thông báo của lần lưu trước, sau đó xóa khỏi session.
        $profileMessage = '';
        $profileMessageType = 'success';

        if (isset($_SESSION['profile_message'])) {
            $profileMessage = $_SESSION['profile_message'];
        }

        if (isset($_SESSION['profile_message_type'])) {
            if ($_SESSION['profile_message_type'] === 'danger') {
                $profileMessageType = 'danger';
            }
        }

        unset(
            $_SESSION['profile_message'],
            $_SESSION['profile_message_type']
        );
        
        $profileStatistics = $userModel->getProfileStatistics($userId);

        $viewData = [
            'title' => 'Hồ sơ cá nhân',
            'userInformation' => $userInformation,
            'csrfToken' => $_SESSION['csrf_token'],
            'isProfilePage' => true,
            'profileMessage' => $profileMessage,
            'profileMessageType' => $profileMessageType,
            'profileStatistics' => $profileStatistics,
        ];

        $this->render('profile/index', $viewData);
    }

    // Kiểm tra form và cập nhật hồ sơ của tài khoản đang đăng nhập.
    public function update(): void
    {
        if (isset($_SESSION['user_id']) === false) {
            throw new HttpException(401, 'Vui lòng đăng nhập để sửa hồ sơ.');
        }

        $userId = (int) $_SESSION['user_id'];

        $userModel = new User();
        $userInformation = $userModel->findById($userId);

        if ($userInformation === null) {
            throw new HttpException(401, 'Tài khoản không tồn tại.');
        }

        // Chỉ nhận các trường hồ sơ được phép sửa và mã CSRF.
        $allowedFields = [
            'full_name',
            'email',
            'phone',
            'bio',
            'csrf_token',
        ];

        foreach ($_POST as $fieldName => $fieldValue) {
            if (in_array($fieldName, $allowedFields, true) === false) {
                throw new HttpException(400, 'Form chứa trường không được phép sửa.');
            }
        }

        $submittedToken = '';

        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
            $submittedToken = $_POST['csrf_token'];
        }

        if (isset($_SESSION['csrf_token']) === false) {
            throw new HttpException(400, 'Phiên làm việc không hợp lệ.');
        }

        if (hash_equals($_SESSION['csrf_token'], $submittedToken) === false) {
            throw new HttpException(400, 'Form không hợp lệ. Vui lòng tải lại trang.');
        }

        $fullName = '';
        $email = '';

        if (isset($_POST['full_name']) && is_string($_POST['full_name'])) {
            $fullName = trim($_POST['full_name']);
        }

        if (isset($_POST['email']) && is_string($_POST['email'])) {
            $email = strtolower(trim($_POST['email']));
        }

        if ($fullName === '' || $email === '') {
            $this->redirectToProfile(
                'Vui lòng nhập đầy đủ họ tên và email.',
                'danger'
            );
        }

        if (mb_strlen($fullName, 'UTF-8') > 120) {
            $this->redirectToProfile(
                'Họ tên không được vượt quá 120 ký tự.',
                'danger'
            );
        }

        if (strlen($email) > 254) {
            $this->redirectToProfile(
                'Email không được vượt quá 254 ký tự.',
                'danger'
            );
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->redirectToProfile(
                'Email không đúng định dạng.',
                'danger'
            );
        }

        $phone = null;
        $biography = null;

        // Trường tùy chọn để trống sẽ được lưu thành NULL.
        if (isset($_POST['phone'])) {
            if (is_string($_POST['phone']) === false) {
                throw new HttpException(400, 'Số điện thoại không hợp lệ.');
            }

            $phone = trim($_POST['phone']);

            if ($phone === '') {
                $phone = null;
            }
        }

        if (isset($_POST['bio'])) {
            if (is_string($_POST['bio']) === false) {
                throw new HttpException(400, 'Thông tin giới thiệu không hợp lệ.');
            }

            $biography = trim($_POST['bio']);

            if ($biography === '') {
                $biography = null;
            }
        }

        if ($phone !== null) {
            if (mb_strlen($phone, 'UTF-8') > 20) {
                $this->redirectToProfile(
                    'Số điện thoại không được vượt quá 20 ký tự.',
                    'danger'
                );
            }

            if (preg_match('/^\+?[0-9][0-9 ()-]*$/', $phone) !== 1) {
                $this->redirectToProfile(
                    'Số điện thoại chỉ nhận chữ số, dấu + ở đầu, khoảng trắng, dấu ngoặc và dấu gạch ngang.',
                    'danger'
                );
            }
        }

        if ($biography !== null) {
            if (mb_strlen($biography, 'UTF-8') > 300) {
                $this->redirectToProfile(
                    'Giới thiệu không được vượt quá 300 ký tự.',
                    'danger'
                );
            }
        }

        try {
            $userModel->updateProfile(
                $userId,
                $fullName,
                $email,
                $phone,
                $biography
            );
        } catch (PDOException $exception) {
            // MariaDB dùng mã 1062 khi email vi phạm ràng buộc không trùng.
            if (
                isset($exception->errorInfo[1])
                && (int) $exception->errorInfo[1] === 1062
            ) {
                $this->redirectToProfile(
                    'Email này đã được tài khoản khác sử dụng.',
                    'danger'
                );
            }

            $this->redirectToProfile(
                'Chưa thể cập nhật hồ sơ. Vui lòng thử lại sau.',
                'danger'
            );
        }

        $this->redirectToProfile(
            'Cập nhật hồ sơ thành công.',
            'success'
        );
    }

    // Lưu thông báo vào session rồi chuyển về Profile.
    private function redirectToProfile(
        string $message,
        string $messageType
    ): void {
        $_SESSION['profile_message'] = $message;
        $_SESSION['profile_message_type'] = $messageType;

        header('Location: ' . createRouteUrl('profile'));
        exit;
    }
}