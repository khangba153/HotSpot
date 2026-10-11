<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\Database;

final class HomeController extends Controller
{
    public function index(): void
    {
        // Xác nhận PDO kết nối được; chưa truy vấn dữ liệu nghiệp vụ.
        Database::getConnection();

        $viewData = [
            'title' => 'Trang chủ',
        ];

        $this->render('home/index', $viewData);
    }
}