<?php
declare(strict_types=1);

use HotSpot\Controllers\AuthController;
use HotSpot\Controllers\HomeController;
use HotSpot\Controllers\ProfileController;
use HotSpot\Core\Router;
use HotSpot\Controllers\PlaceSearchController;

$router = new Router();

$homeController = new HomeController();
$profileController = new ProfileController();
$placeSearchController = new PlaceSearchController();
$authController = new AuthController();

// Các trang hiển thị.
$router->add('GET', 'home', $homeController, 'index');
$router->add('GET', 'api/places/search', $placeSearchController, 'search');
$router->add('GET', 'profile', $profileController, 'index');
$router->add('GET', 'login', $authController, 'showLogin');

// Nhận và xử lý form đăng nhập.
$router->add('POST', 'login', $authController, 'login');
// Xử lý đăng xuất.
$router->add('POST', 'logout', $authController, 'logout');

// Nhận form cập nhật hồ sơ.
$router->add('POST', 'profile/update', $profileController, 'update');

return $router;
