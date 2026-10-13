<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\PlaceSearch;

final class PlaceSearchController extends Controller
{
    public function search(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $rawKeyword = $_GET['keyword'] ?? '';
        $keyword = is_string($rawKeyword) ? trim($rawKeyword) : '';
        $keyword = preg_replace('/\s+/u', ' ', $keyword) ?? $keyword;

        if (function_exists('mb_substr')) {
            $keyword = mb_substr($keyword, 0, 100, 'UTF-8');
        } else {
            $characters = preg_split(
                '//u',
                $keyword,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            $keyword = is_array($characters)
                ? implode('', array_slice($characters, 0, 100))
                : substr($keyword, 0, 100);
        }

        try {
            $model = new PlaceSearch();

            $result = $model->searchApprovedForHome(
                $keyword !== '' ? $keyword : null
            );

            // Dùng lại View card có sẵn, không viết HTML card lần nữa.
            $places = $result['places'];

            ob_start();

            try {
                require dirname(__DIR__) . '/views/home/_place_cards.php';
                $html = ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Tìm kiếm thành công.',
                'data' => [
                    'html' => $html,
                    'total_count' => $result['total_count'],
                    'keyword' => $keyword,
                ],
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } catch (\Throwable $exception) {
            error_log(
                'PlaceSearch API error: ' . $exception->getMessage()
            );

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Không thể tìm kiếm địa điểm lúc này.',
                'data' => null,
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}
