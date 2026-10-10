<?php
declare(strict_types=1);
use HotSpot\Core\Router;
use HotSpot\Core\View;
use HotSpot\Controllers\AuthController;
use HotSpot\Controllers\HomeController;
$router=new Router();
$auth=new AuthController();
$router->add('GET','home',[new HomeController(),'index']);
$router->add('GET','auth/register',[$auth,'registerForm']);
$router->add('POST','auth/register',[$auth,'register']);
$router->add('GET','auth/login',[$auth,'loginForm']);
$router->add('POST','auth/login',[$auth,'login']);
$router->add('POST','auth/logout',[$auth,'logout']);
// Access gate only; TV4 implements the Admin module later.
$router->add('GET','admin',static function (): void {
    $user=must_admin();
    View::render('admin/index',['title'=>'Quản trị','user'=>$user]);
});
return $router;
