<?php
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
