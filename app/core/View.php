<?php
declare(strict_types=1);
namespace HotSpot\Core;

use RuntimeException;

final class View
{
    public static function render(string $template, array $data = [], int $status = 200): void
    {
        $file = dirname(__DIR__) . '/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Thiếu view: ' . $template);
        }
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        $render = static function (string $file, array $data): string {
            extract($data, EXTR_SKIP);
            ob_start();
            require $file;
            return (string) ob_get_clean();
        };
        $content = $render($file, $data);
        $title = $data['title'] ?? 'Hot Spot';
        require dirname(__DIR__) . '/views/layouts/main.php';
    }
}
