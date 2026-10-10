<?php
declare(strict_types=1);
namespace HotSpot\Controllers;
use HotSpot\Core\Controller;
use HotSpot\Core\Database;
use Throwable;
final class HomeController extends Controller
{
    public function index(): void
    {
        try {
            Database::connection();
            $this->render('home/index', ['title'=>'Trang chủ']);
        } catch (Throwable $e) {
            $this->render('errors/status', ['title'=>'Thông báo', 'status'=>503,
                'message'=>'Chưa thể tải trang. Vui lòng thử lại sau.'], 503);
        }
    }
}
