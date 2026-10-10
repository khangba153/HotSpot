<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'HotSpot\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $segments = explode('\\', substr($class, strlen($prefix)));
    $name = array_pop($segments);
    $dirs = array_map('strtolower', $segments);
    $path = __DIR__ . '/' . implode('/', $dirs);
    $target = $path . '/' . $name . '.php';
    if (is_file($target)) {
        require_once $target;
    }
});
