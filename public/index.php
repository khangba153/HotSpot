<?php
declare(strict_types=1);

use HotSpot\Core\HttpException;
use HotSpot\Core\View;

require_once dirname(__DIR__) . '/app/bootstrap.php';
header('Content-Type: text/html; charset=UTF-8');

try {
    $routeName = 'home';

    if (isset($_GET['route'])) {
        if (is_string($_GET['route']) === false) {
            throw new HttpException(400, 'Đường dẫn không hợp lệ.');
        }

        $routeName = trim($_GET['route'], '/');

        if ($routeName === '') {
            $routeName = 'home';
        }
    }

    $httpMethod = 'GET';

    if (isset($_SERVER['REQUEST_METHOD'])) {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
    }

    $router = require dirname(__DIR__) . '/app/routes.php';
    $router->dispatch($routeName, $httpMethod);
} catch (HttpException $exception) {
    $httpStatusCode = $exception->getCode();
    http_response_code($httpStatusCode);

    $errorViewData = [
        'title' => 'Thông báo',
        'status' => $httpStatusCode,
        'message' => $exception->getMessage(),
    ];

    View::render('errors/status', $errorViewData);
} catch (Throwable $exception) {
    http_response_code(500);

    $errorViewData = [
        'title' => 'Thông báo',
        'status' => 500,
        'message' => 'Chưa thể tải trang. Vui lòng thử lại sau.',
    ];

    View::render('errors/status', $errorViewData);
}