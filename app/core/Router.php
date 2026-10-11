<?php
declare(strict_types=1);

namespace HotSpot\Core;

final class Router
{
    private array $registeredRoutes = [];

    public function add(
        string $httpMethod,
        string $routeName,
        Controller $controllerObject,
        string $controllerMethodName
    ): void {
        $httpMethod = strtoupper($httpMethod);

        $routeInformation = [
            'controller' => $controllerObject,
            'methodName' => $controllerMethodName,
        ];

        $this->registeredRoutes[$routeName][$httpMethod] = $routeInformation;
    }

    public function dispatch(string $routeName, string $httpMethod): void
    {
        if (isset($this->registeredRoutes[$routeName]) === false) {
            throw new HttpException(404, 'Không tìm thấy trang.');
        }

        $httpMethod = strtoupper($httpMethod);
        $registeredMethods = $this->registeredRoutes[$routeName];

        if (isset($registeredMethods[$httpMethod]) === false) {
            $allowedHttpMethods = array_keys($registeredMethods);
            $allowedMethodsText = implode(', ', $allowedHttpMethods);

            header('Allow: ' . $allowedMethodsText);
            throw new HttpException(405, 'Phương thức truy cập không được hỗ trợ.');
        }

        $routeInformation = $registeredMethods[$httpMethod];
        $controllerObject = $routeInformation['controller'];
        $controllerMethodName = $routeInformation['methodName'];

        // Gọi phương thức đã đăng ký, ví dụ $homeController->index().
        $controllerObject->$controllerMethodName();
    }
}