# Pig Router

一个简单轻量的PHP路由库，支持HTTP方法、路由分组、中间件和参数处理。

**注意：** 此仓库包含多语言README。请查看其他语言版本以获取更多信息。

- [English](README.md)
- [中文](README_zh.md)

## 安装

通过Composer安装：

```bash
composer require pig/router
```

## 使用

### 基本示例

```php
$router = new \Pig\Router\Router();
try {
    $router->loadRoutes(__DIR__ . "/api.php");
    $router->dispatch();
} catch (\Pig\Router\NotFoundException $e) {
    // 处理404
    echo "404 Not Found";
} catch (\Pig\Router\InvalidCallbackException $e) {
    echo $e->getMessage();
}
```

### 定义路由

创建一个`api.php`文件来定义路由：

```php
/**
 * @var \Pig\Router\Router $router
 */

// 简单路由
$router->get('/home', function() {
    echo "欢迎回家";
});

$router->post('/submit', [\App\Controllers\FormController::class, 'submit']);

// 带参数的路由
$router->get('/user/{id}', function($id) {
    echo "用户ID: " . $id;
});

// 使用正则参数
$router->get('/user/(\d+)', function($userId) {
    echo "用户ID: " . $userId;
});

// 路由分组
$router->group('/api', [], function (\Pig\Router\Router $router) {
    $router->get('/users', [\App\Controllers\UserController::class, 'index']);
    $router->post('/users', [\App\Controllers\UserController::class, 'create']);
    $router->get('/users/{id}', [\App\Controllers\UserController::class, 'show']);
});
```

### HTTP方法

路由器支持以下HTTP方法：

- `get($pattern, $callback)`
- `post($pattern, $callback)`
- `put($pattern, $callback)`
- `delete($pattern, $callback)`
- `patch($pattern, $callback)`
- `head($pattern, $callback)`
- `options($pattern, $callback)`
- `get_post($pattern, $callback)` - 接受GET和POST
- `any($pattern, $callback)` - 接受所有方法

### 路由参数

- 命名参数: `/user/{id}` - 在回调中捕获为 `$id`
- 正则参数: `/user/(\d+)` - 捕获为位置参数

### 中间件

为路由或组添加中间件：

```php
$router->get('/admin', function() {
    echo "管理面板";
})->middleware([\App\Middleware\AuthMiddleware::class, 'check']);

$router->group('/admin', [\App\Middleware\AuthMiddleware::class], function ($router) {
    $router->get('/dashboard', [\App\Controllers\AdminController::class, 'dashboard']);
});
```

您还可以添加全局的前后中间件：

```php
$router->before([\App\Middleware\LoggingMiddleware::class, 'logRequest']);
$router->after([\App\Middleware\LoggingMiddleware::class, 'logResponse']);
```

中间件可以是：
- 可调用的函数
- 具有`handle()`方法的类
- 中间件数组

### 路由分组

为路由分组设置通用前缀和中间件：

```php
$router->group('/api/v1', [\App\Middleware\ApiMiddleware::class], function ($router) {
    $router->get('/users', [\App\Controllers\Api\UserController::class, 'index']);
    $router->post('/users', [\App\Controllers\Api\UserController::class, 'create']);
});
```

### 回调

回调可以是：
- 匿名函数
- `Controller@method` 字符串（例如 `'App\Controllers\UserController@index'`）
- `[Controller::class, 'method']` 数组
- 具有`handle()`方法的对象

### 兼容模式

为了与其他框架兼容：

```php
$router->compatible_mode('r'); // 使用 $_GET['r'] 进行路由
```

### 手动调度

您可以手动调度路由用于测试或CLI使用：

```php
$result = $router->dispatch('GET', '/home');
```

## API参考

### Router类

#### 方法

- `get(string $pattern, callable|array|string $callback)`: 注册GET路由
- `post(string $pattern, callable|array|string $callback)`: 注册POST路由
- `put(string $pattern, callable|array|string $callback)`: 注册PUT路由
- `delete(string $pattern, callable|array|string $callback)`: 注册DELETE路由
- `patch(string $pattern, callable|array|string $callback)`: 注册PATCH路由
- `head(string $pattern, callable|array|string $callback)`: 注册HEAD路由
- `options(string $pattern, callable|array|string $callback)`: 注册OPTIONS路由
- `get_post(string $pattern, callable|array|string $callback)`: 注册GET和POST路由
- `any(string $pattern, callable|array|string $callback)`: 注册所有HTTP方法路由
- `group(string $prefix, array|string $middleware, callable $callback)`: 分组路由
- `dispatch(string|null $method, string|null $uri)`: 调度请求
- `loadRoutes(string $file)`: 从文件加载路由
- `compatible_mode(string $string)`: 启用兼容模式
- `before(mixed $middleware)`: 添加全局前中间件
- `after(mixed $middleware)`: 添加全局后中间件

### Route类

#### 方法

- `middleware(array|string $middleware)`: 为路由添加中间件

### 异常

- `NotFoundException`: 当没有路由匹配时抛出
- `InvalidCallbackException`: 当回调无效时抛出
- `MethodNotFoundException`: 当控制器中方法不存在时抛出

## 测试

路由器包含使用 PHPUnit 的全面测试套件。

### 运行测试

安装依赖并运行测试：

```bash
composer install
composer test
```

或直接运行 PHPUnit：

```bash
vendor/bin/phpunit
```

### 测试覆盖

测试套件涵盖：

- 所有 HTTP 方法的路由注册
- 带参数和不带参数的路由分发
- 路由分组和中间件
- 异常处理
- 兼容模式功能
- CLI 分发
- 控制器回调（数组和字符串格式）
- 前后中间件

## 许可证

本项目根据MIT许可证授权 - 详情请见[LICENSE](LICENSE)文件。

## 作者

sn01615
