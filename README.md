### install
```
composer require pig/router
```

example 0:
```php
$router = new \Pig\Router\Router();
try {
    $router->loadRoutes(__DIR__ . "/api.php");
    $router->dispatch();
} catch (\Pig\Router\NotFoundException $e) {
    # 404
} catch (\Pig\Router\InvalidCallbackException $e) {
    echo $e->getMessage();
}
```
api.php:
```php
/**
 * @var \Pig\Router\Router $router
 */
$router->get('/test/test1', [\pilots\TestController::class, 'test01']);
$router->group('/test', [], function (\Pig\Router\Router $router) {
    $router->post('/create', [\pilots\UserController::class, 'create01']);
    // 带参数的路由
    $router->get('/info/(\w+)', [\pilots\UserController::class, 'info01']);
    $router->get('/info/{name}', [\pilots\UserController::class, 'info01']);
});
```
