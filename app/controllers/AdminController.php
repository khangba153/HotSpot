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

        $this->render('admin/places', [
            'title' => 'Địa điểm chờ duyệt',
            'userInformation' => $user,
            'pendingPlaces' => (new PlaceModeration())->findPendingPlaces(),
            'isAdminPage' => true,
        ]);
    }
}
