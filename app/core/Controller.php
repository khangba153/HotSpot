<?php
declare(strict_types=1);

namespace HotSpot\Core;

abstract class Controller
{
    protected function render(string $viewName, array $viewData = []): void
    {
        View::render($viewName, $viewData);
    }
}