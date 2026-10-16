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
            'place' => $place, 'imageUrls' => $images, 'isAdminPage' => true,
        ]);
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
}
