<?php
declare(strict_types=1);
namespace HotSpot\Core;
abstract class Controller
{
    protected function render(string $view, array $data = [], int $status = 200): void
    {
        View::render($view, $data, $status);
    }
}
