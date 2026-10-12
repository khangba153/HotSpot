<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\User;

final class AuthController extends Controller
{
    // Hiển thị form đăng nhập và thông báo lỗi nếu có.
    public function showLogin(
        string $errorMessage = '',
        string $email = ''
    ): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . createRouteUrl('profile'));
            exit;
        }

        // Token giúp kiểm tra form được gửi từ phiên làm việc hiện tại.
        if (isset($_SESSION['csrf_token']) === false) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $viewData = [
            'title' => 'Đăng nhập',
            'errorMessage' => $errorMessage,
            'email' => $email,
            'csrfToken' => $_SESSION['csrf_token'],
            'isLoginPage' => true,
        ];

        $this->render('auth/login', $viewData);
    }

    // Kiểm tra dữ liệu form, xác thực mật khẩu và lưu phiên đăng nhập.
    public function login(): void
    {
        $submittedToken = '';

        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
            $submittedToken = $_POST['csrf_token'];
        }

        if (isset($_SESSION['csrf_token']) === false) {
            throw new HttpException(
                400,
                'Phiên làm việc không hợp lệ. Vui lòng mở lại trang đăng nhập.'
            );
        }

        if (hash_equals($_SESSION['csrf_token'], $submittedToken) === false) {
            throw new HttpException(
                400,
                'Form không hợp lệ. Vui lòng mở lại trang đăng nhập.'
            );
        }

        $email = '';
        $password = '';

        if (isset($_POST['email']) && is_string($_POST['email'])) {
            $email = trim($_POST['email']);
        }

        if (isset($_POST['password']) && is_string($_POST['password'])) {
            $password = $_POST['password'];
        }

        if ($email === '' || $password === '') {
            $this->showLogin('Vui lòng nhập email và mật khẩu.', $email);
            return;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->showLogin('Email không đúng định dạng.', $email);
            return;
        }

        $userModel = new User();
        $userInformation = $userModel->findByEmail($email);

        if ($userInformation === null) {
            $this->showLogin('Email hoặc mật khẩu không đúng.', $email);
            return;
        }

        $passwordIsCorrect = password_verify(
            $password,
            $userInformation['password_hash']
        );

        if ($passwordIsCorrect === false) {
            $this->showLogin('Email hoặc mật khẩu không đúng.', $email);
            return;
        }

        // Đổi mã phiên sau khi đăng nhập để tránh dùng lại mã phiên cũ.
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userInformation['user_id'];

        header('Location: ' . createRouteUrl('profile'));
        exit;
    }

        // Kiểm tra form, xóa phiên đăng nhập và quay về trang login.
    public function logout(): void
    {
        $submittedToken = '';

        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
            $submittedToken = $_POST['csrf_token'];
        }

        if (isset($_SESSION['csrf_token']) === false) {
            throw new HttpException(400, 'Phiên làm việc không hợp lệ.');
        }

        if (hash_equals($_SESSION['csrf_token'], $submittedToken) === false) {
            throw new HttpException(400, 'Form đăng xuất không hợp lệ.');
        }

        $_SESSION = [];
        session_destroy();

        header('Location: ' . createRouteUrl('login'));
        exit;
    }
}