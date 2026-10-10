<?php
declare(strict_types=1);

use HotSpot\Core\HttpException;
use HotSpot\Core\View;

require dirname(__DIR__) . '/app/bootstrap.php';

$path = $_GET['route'] ?? 'home';
$path = is_string($path) ? $path : '';
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$isApi = str_starts_with($path, 'api/');

try {
    if ($method === 'POST') {
        verify_csrf();
    }
    /** @var HotSpot\Core\Router $router */
    $router = require dirname(__DIR__) . '/app/routes.php';
    $router->dispatch($method, $path);
} catch (HttpException $e) {
    if ($isApi) {
        json_response(false, $e->getMessage(), null, [], $e->status());
    }
    View::render('errors/status', ['title' => 'Thông báo', 'message' => $e->getMessage(), 'status' => $e->status()], $e->status());
} catch (Throwable $e) {
    error_log('[HotSpot unexpected] ' . $e->getMessage());
    if ($isApi) {
        json_response(false, 'Lỗi máy chủ. Vui lòng thử lại sau.', null, [], 500);
    }
    View::render('errors/status', ['title' => 'Lỗi hệ thống', 'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.', 'status' => 500], 500);
}
