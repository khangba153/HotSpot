<?php
declare(strict_types=1);

use HotSpot\Controllers\AuthController;
use HotSpot\Controllers\HomeController;
use HotSpot\Controllers\PlaceController;
use HotSpot\Controllers\ProfileController;
use HotSpot\Core\Router;
use HotSpot\Controllers\PlaceSearchController;
use HotSpot\Controllers\AdminController;

$router = new Router();

$homeController = new HomeController();
$profileController = new ProfileController();
$placeSearchController = new PlaceSearchController();
$placeController = new PlaceController();
$authController = new AuthController();
$adminController = new AdminController();

// Các trang hiển thị.
$router->add('GET', 'home', $homeController, 'index');
$router->add('GET', 'api/places/search', $placeSearchController, 'search');
// Trang chi tiết địa điểm (C01: card Home link tới places/show&id=...).
$router->add('GET', 'places/show', $placeController, 'show');
$router->add('GET', 'profile', $profileController, 'index');
$router->add('GET', 'admin/places', $adminController, 'places');
$router->add('GET', 'admin/places/review', $adminController, 'review');
$router->add('POST', 'admin/places/status', $adminController, 'updatePlaceStatus');
$router->add('GET', 'login', $authController, 'showLogin');

// Nhận và xử lý form đăng nhập.
$router->add('POST', 'login', $authController, 'login');
// Xử lý đăng xuất.
$router->add('POST', 'logout', $authController, 'logout');

// Nhận form cập nhật hồ sơ.
$router->add('POST', 'profile/update', $profileController, 'update');

return $router;
