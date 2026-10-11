<?php
declare(strict_types=1);

use HotSpot\Controllers\HomeController;
use HotSpot\Core\Router;

$router = new Router();
$homeController = new HomeController();

$router->add('GET', 'home', $homeController, 'index');

return $router;