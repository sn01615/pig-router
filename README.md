example 1:
```php
require dirname(__DIR__) . '/vendor/autoload.php';
include __DIR__ . '/CorsMiddleware.php';

$router = new \Pig\Router\Router();

// 基本路由
$router->get('/', function () {
    echo "Welcome to homepage!";
});

$router->get('/about', function () {
    echo "About page";
});

// 带参数的路由
$router->get('/user/{id}', function ($id) {
    echo "User ID: " . htmlspecialchars($id);
});

$router->get('/post/{id}/comment/{commentId}', function ($id, $commentId) {
    echo "Post ID: " . htmlspecialchars($id) . ", Comment ID: " . htmlspecialchars($commentId);
});

// POST 路由
$router->post('/user/create', function () {
    echo "Creating user...";
});

// 需要认证的路由
$router->get('/profile', function () {
    echo "User profile";
})->middleware(\AuthMiddleware::class);

// 路由组
$router->group('/api', [\CorsMiddleware::class], function ($router) {
    $router->get('/users', function () {
        header('Content-Type: application/json');
        echo json_encode(['users' => []]);
    });

    $router->get('/posts', function () {
        header('Content-Type: application/json');
        echo json_encode(['posts' => []]);
    });
});

// 分发请求
$router->dispatch();

```
example 2:
```php
require dirname(__DIR__) . '/vendor/autoload.php';

class UserController
{
    public function index()
    {
        echo "All users";
    }

    public function show($id)
    {
        echo "Show user with ID: " . htmlspecialchars($id);
    }

    public function create()
    {
        echo "Create new user";
    }

    public function update($id)
    {
        echo "Update user with ID: " . htmlspecialchars($id);
    }

    public function delete($id)
    {
        echo "Delete user with ID: " . htmlspecialchars($id);
    }
}

// 在路由中使用控制器
$router = new \Pig\Router\Router();

$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');

//$router->get('/tempfile_1764506562513.php', 'UserController@index');
$router->dispatch();

```
