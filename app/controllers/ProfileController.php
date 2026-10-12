<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\User;

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

        $viewData = [
            'title' => 'Hồ sơ cá nhân',
            'userInformation' => $userInformation,
            'csrfToken' => $_SESSION['csrf_token'],
            'isProfilePage' => true,
        ];

        $this->render('profile/index', $viewData);
    }
}