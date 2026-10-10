<?php
declare(strict_types=1);
namespace HotSpot\Core;

final class Router
{
    /** @var array<string,array<string,callable>> */
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): self
    {
        $this->routes[$path][strtoupper($method)] = $handler;
        return $this;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        if (!isset($this->routes[$path])) {
            throw new HttpException(404, 'Không tìm thấy trang.');
        }
        if (!isset($this->routes[$path][$method])) {
            header('Allow: ' . implode(', ', array_keys($this->routes[$path])));
            throw new HttpException(405, 'Phương thức HTTP không được hỗ trợ.');
        }
        ($this->routes[$path][$method])();
    }
}
