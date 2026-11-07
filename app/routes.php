<?php
declare(strict_types=1);

use HotSpot\Controllers\AuthController;
use HotSpot\Controllers\HomeController;
use HotSpot\Controllers\PlaceController;
use HotSpot\Controllers\ProfileController;
use HotSpot\Core\Router;
use HotSpot\Controllers\PlaceSearchController;
use HotSpot\Controllers\AdminController;
use HotSpot\Controllers\FavoriteController;
use HotSpot\Controllers\ReviewController;

$router = new Router();

$homeController = new HomeController();
$profileController = new ProfileController();
$placeSearchController = new PlaceSearchController();
$placeController = new PlaceController();
$authController = new AuthController();
$adminController = new AdminController();
$favoriteController = new FavoriteController();
$reviewController = new ReviewController();

// Các trang hiển thị.
$router->add('GET', 'home', $homeController, 'index');
$router->add('GET', 'api/places/search', $placeSearchController, 'search');
// Trang chi tiết địa điểm (C01: card Home link tới places/show&id=...).
$router->add('GET', 'places/show', $placeController, 'show');
$router->add('GET', 'places/create', $placeController, 'create');
$router->add('POST', 'places/store', $placeController, 'store');
$router->add('GET', 'places/edit', $placeController, 'edit');
$router->add('POST', 'places/update', $placeController, 'update');
$router->add('POST', 'places/resubmit', $placeController, 'resubmit');
$router->add('GET', 'places/mine', $placeController, 'mine');
$router->add('GET', 'profile', $profileController, 'index');
$router->add('GET', 'admin', $adminController, 'dashboard');
$router->add('GET', 'admin/categories', $adminController, 'categories');
$router->add('POST', 'admin/categories/store', $adminController, 'storeCategory');
$router->add('POST', 'admin/categories/update', $adminController, 'updateCategory');
$router->add('POST', 'admin/categories/status', $adminController, 'updateCategoryStatus');
$router->add('GET', 'admin/reviews', $adminController, 'reviews');
$router->add('POST', 'admin/reviews/status', $adminController, 'updateReviewStatus');
$router->add('GET', 'admin/places', $adminController, 'places');
$router->add('GET', 'admin/places/review', $adminController, 'review');
$router->add('POST', 'admin/places/status', $adminController, 'updatePlaceStatus');
$router->add('GET', 'login', $authController, 'showLogin');
$router->add('GET', 'favorites', $favoriteController, 'index');
$router->add('GET', 'register', $authController, 'showRegister');
$router->add('POST', 'register', $authController, 'register');

// Nhận và xử lý form đăng nhập.
$router->add('POST', 'login', $authController, 'login');

// Nhận token từ nút đăng nhập Google.
$router->add('POST', 'login/google', $authController, 'googleLogin');

// Hiển thị form yêu cầu đặt lại mật khẩu.
$router->add(
    'GET',
    'forgot-password',
    $authController,
    'showForgotPassword'
);

// Mở form đặt mật khẩu mới từ liên kết trong email.
$router->add(
    'GET',
    'reset-password',
    $authController,
    'showResetPassword'
);

// Xử lý yêu cầu gửi liên kết đặt lại mật khẩu.
$router->add(
    'POST',
    'forgot-password',
    $authController,
    'requestPasswordReset'
);

// Lưu mật khẩu mới từ form khôi phục.
$router->add(
    'POST',
    'reset-password',
    $authController,
    'resetPassword'
);

// Xử lý đăng xuất.
$router->add('POST', 'logout', $authController, 'logout');

// Nhận form cập nhật hồ sơ.
$router->add('POST', 'profile/update', $profileController, 'update');

// Nhận yêu cầu thêm hoặc bỏ yêu thích.
$router->add('POST', 'favorites/set', $favoriteController, 'set');

// Nhận yêu cầu gửi hoặc sửa đánh giá.
$router->add('POST', 'reviews/save', $reviewController, 'save');

return $router;
