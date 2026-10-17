<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\PlaceSearch;
use HotSpot\Models\User;

final class HomeController extends Controller
{
    public function index(): void
    {
        $rawKeyword = $_GET['keyword'] ?? '';
        $keyword = is_string($rawKeyword) ? trim($rawKeyword) : '';

        // Chuẩn hóa nhiều khoảng trắng thành một khoảng trắng; không sửa nội dung khác.
        $normalizedKeyword = preg_replace('/\s+/u', ' ', $keyword);
        if ($normalizedKeyword !== null) {
            $keyword = $normalizedKeyword;
        }

        // Giới hạn 100 ký tự Unicode để tránh query quá dài.
        if (function_exists('mb_substr')) {
            $keyword = mb_substr($keyword, 0, 100, 'UTF-8');
        } else {
            $characters = preg_split('//u', $keyword, -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($characters)) {
                $keyword = implode('', array_slice($characters, 0, 100));
            } else {
                // Dự phòng cho input UTF-8 lỗi định dạng.
                $keyword = substr($keyword, 0, 100);
            }
        }

        $placeSearch = new PlaceSearch();
        $searchResult = $placeSearch->searchApprovedForHome(
            $keyword !== '' ? $keyword : null
        );

        $userInformation = null;
        if (isset($_SESSION['user_id'])) {
            $userInformation = (new User())->findById((int) $_SESSION['user_id']);
        }

        $viewData = [
            'title' => 'Khám phá Việt Nam',
            'places' => $searchResult['places'],
            'totalCount' => $searchResult['total_count'],
            'keyword' => $keyword,
            'categories' => $placeSearch->findActiveCategories(),
            'userInformation' => $userInformation,
        ];

        $this->render('home/index', $viewData);
    }
}
