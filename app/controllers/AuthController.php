<?php

declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\User;
use PDOException;
use Throwable;

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

    // Hiển thị form đăng ký, giữ lại họ tên và email khi nhập lỗi.
    public function showRegister(
        string $errorMessage = '',
        string $fullName = '',
        string $email = ''
    ): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . createRouteUrl('profile'));
            exit;
        }

        if (isset($_SESSION['csrf_token']) === false) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $viewData = [
            'title' => 'Đăng ký tài khoản',
            'errorMessage' => $errorMessage,
            'fullName' => $fullName,
            'email' => $email,
            'csrfToken' => $_SESSION['csrf_token'],
            'isLoginPage' => true,
        ];

        $this->render('auth/register', $viewData);
    }

    // Kiểm tra form, tạo tài khoản Member và đăng nhập sau khi đăng ký thành công.
    public function register(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . createRouteUrl('profile'));
            exit;
        }

        $allowedFields = [
            'csrf_token',
            'full_name',
            'email',
            'password',
            'password_confirmation',
        ];

        foreach ($_POST as $fieldName => $fieldValue) {
            if (in_array($fieldName, $allowedFields, true) === false) {
                throw new HttpException(
                    400,
                    'Form chứa trường không được phép.'
                );
            }
        }

        $submittedToken = '';

        if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
            $submittedToken = $_POST['csrf_token'];
        }

        if (
            isset($_SESSION['csrf_token']) === false
            || hash_equals($_SESSION['csrf_token'], $submittedToken) === false
        ) {
            throw new HttpException(
                400,
                'Form không hợp lệ. Vui lòng tải lại trang đăng ký.'
            );
        }

        $registrationData = [
            'full_name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        foreach ($registrationData as $fieldName => $fieldValue) {
            if (isset($_POST[$fieldName])) {
                if (is_string($_POST[$fieldName]) === false) {
                    throw new HttpException(
                        400,
                        'Dữ liệu đăng ký không hợp lệ.'
                    );
                }

                $registrationData[$fieldName] = $_POST[$fieldName];
            }
        }

        $fullName = trim($registrationData['full_name']);
        $email = strtolower(trim($registrationData['email']));
        $password = $registrationData['password'];
        $passwordConfirmation = $registrationData['password_confirmation'];

        $errorMessage = $this->validateRegistrationData(
            $fullName,
            $email,
            $password,
            $passwordConfirmation
        );

        if ($errorMessage !== '') {
            http_response_code(422);
            $this->showRegister($errorMessage, $fullName, $email);
            return;
        }

        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $userModel = new User();
            $userId = $userModel->createUser(
                $fullName,
                $email,
                $passwordHash
            );
        } catch (PDOException $exception) {
            // Unique email trong database chặn cả trường hợp gửi trùng đồng thời.
            if (
                isset($exception->errorInfo[1])
                && (int) $exception->errorInfo[1] === 1062
            ) {
                http_response_code(409);
                $this->showRegister(
                    'Email này đã được sử dụng.',
                    $fullName,
                    $email
                );
                return;
            }

            http_response_code(500);
            $this->showRegister(
                'Chưa thể tạo tài khoản. Vui lòng thử lại sau.',
                $fullName,
                $email
            );
            return;
        } catch (Throwable $exception) {
            http_response_code(500);
            $this->showRegister(
                'Chưa thể tạo tài khoản. Vui lòng thử lại sau.',
                $fullName,
                $email
            );
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: ' . createRouteUrl('profile'), true, 303);
        exit;
    }

    // Trả thông báo lỗi đầu tiên; chuỗi rỗng nghĩa là dữ liệu hợp lệ.
    private function validateRegistrationData(
        string $fullName,
        string $email,
        string $password,
        string $passwordConfirmation
    ): string {
        if (
            trim($fullName) === ''
            || trim($email) === ''
            || $password === ''
            || $passwordConfirmation === ''
        ) {
            return 'Vui lòng nhập đầy đủ thông tin đăng ký.';
        }

        if (mb_strlen($fullName, 'UTF-8') > 120) {
            return 'Họ và tên không được vượt quá 120 ký tự.';
        }

        if (
            strlen($email) > 254
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            return 'Địa chỉ email không hợp lệ.';
        }

        if (mb_strlen($password, 'UTF-8') < 8) {
            return 'Mật khẩu phải có ít nhất 8 ký tự.';
        }

        // Bcrypt chỉ xử lý tối đa 72 byte, không phải 72 ký tự Unicode.
        if (strlen($password) > 72) {
            return 'Mật khẩu quá dài. Vui lòng rút ngắn mật khẩu.';
        }

        if (strpos($password, "\0") !== false) {
            return 'Mật khẩu chứa ký tự không hợp lệ.';
        }

        if (
            preg_match('/[A-Z]/', $password) !== 1
            || preg_match('/[a-z]/', $password) !== 1
            || preg_match('/[0-9]/', $password) !== 1
        ) {
            return 'Mật khẩu phải có chữ hoa, chữ thường và chữ số.';
        }

        if ($password !== $passwordConfirmation) {
            return 'Mật khẩu xác nhận không khớp.';
        }

        return '';
    }
}
