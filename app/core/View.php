<?php
declare(strict_types=1);

namespace HotSpot\Core;

use RuntimeException;
use Throwable;

final class View
{
    public static function render(string $viewName, array $viewData = []): void
    {
        $viewDirectoryPath = dirname(__DIR__) . '/views/';
        $viewFilePath = $viewDirectoryPath . $viewName . '.php';
        $layoutFilePath = $viewDirectoryPath . 'layouts/main.php';

        if (is_file($viewFilePath) === false) {
            throw new RuntimeException('Không tìm thấy View.');
        }

        if (is_file($layoutFilePath) === false) {
            throw new RuntimeException('Không tìm thấy layout.');
        }

        // View truy cập dữ liệu qua $viewData['ten_truong'], không tạo biến ngầm.
        ob_start();

        try {
            require $viewFilePath;
            $pageContent = ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }

        require $layoutFilePath;
    }
}