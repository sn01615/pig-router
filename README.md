# Pig Router

A simple and lightweight PHP routing library that supports HTTP methods, route grouping, middleware, and parameter handling.

## Installation

Install via Composer:

```bash
composer require pig/router
```

## Usage

### Basic Example

```php
$router = new \Pig\Router\Router();
try {
    $router->loadRoutes(__DIR__ . "/api.php");
    $router->dispatch();
} catch (\Pig\Router\NotFoundException $e) {
    // Handle 404
    echo "404 Not Found";
} catch (\Pig\Router\InvalidCallbackException $e) {
    echo $e->getMessage();
}
```

### Defining Routes

Create an `api.php` file to define your routes:

```php
/**
 * @var \Pig\Router\Router $router
 */

// Simple routes
$router->get('/home', function() {
    echo "Welcome Home";
});

$router->post('/submit', [\App\Controllers\FormController::class, 'submit']);

// Routes with parameters
$router->get('/user/{id}', function($id) {
    echo "User ID: " . $id;
});

// Using regex parameters
$router->get('/user/(\d+)', function($userId) {
    echo "User ID: " . $userId;
});

// Route grouping
$router->group('/api', [], function (\Pig\Router\Router $router) {
    $router->get('/users', [\App\Controllers\UserController::class, 'index']);
    $router->post('/users', [\App\Controllers\UserController::class, 'create']);
    $router->get('/users/{id}', [\App\Controllers\UserController::class, 'show']);
});
```

### HTTP Methods

The router supports the following HTTP methods:

- `get($pattern, $callback)`
- `post($pattern, $callback)`
- `put($pattern, $callback)`
- `delete($pattern, $callback)`
- `patch($pattern, $callback)`
- `get_post($pattern, $callback)` - Accepts both GET and POST
- `any($pattern, $callback)` - Accepts all methods

### Route Parameters

- Named parameters: `/user/{id}` - Captured as `$id` in the callback
- Regex parameters: `/user/(\d+)` - Captured as positional parameters

### Middleware

Add middleware to routes or groups:

```php
$router->get('/admin', function() {
    echo "Admin Panel";
})->middleware([\App\Middleware\AuthMiddleware::class, 'check']);

$router->group('/admin', [\App\Middleware\AuthMiddleware::class], function ($router) {
    $router->get('/dashboard', [\App\Controllers\AdminController::class, 'dashboard']);
});
```

Middleware can be:
- A callable function
- A class with a `handle()` method
- An array of middleware

### Route Grouping

Group routes with common prefixes and middleware:

```php
$router->group('/api/v1', [\App\Middleware\ApiMiddleware::class], function ($router) {
    $router->get('/users', [\App\Controllers\Api\UserController::class, 'index']);
    $router->post('/users', [\App\Controllers\Api\UserController::class, 'create']);
});
```

### Callbacks

Callbacks can be:
- Anonymous functions
- `Controller@method` strings (e.g., `'App\Controllers\UserController@index'`)
- `[Controller::class, 'method']` arrays
- Objects with a `handle()` method

### Compatible Mode

For compatibility with certain frameworks:

```php
$router->compatible_mode('r'); // Use $_GET['r'] for routing
```

### Manual Dispatch

You can manually dispatch routes for testing or CLI usage:

```php
$result = $router->dispatch('GET', '/home');
```

## API Reference

### Router Class

#### Methods

- `get(string $pattern, callable|array|string $callback)`: Register a GET route
- `post(string $pattern, callable|array|string $callback)`: Register a POST route
- `put(string $pattern, callable|array|string $callback)`: Register a PUT route
- `delete(string $pattern, callable|array|string $callback)`: Register a DELETE route
- `patch(string $pattern, callable|array|string $callback)`: Register a PATCH route
- `get_post(string $pattern, callable|array|string $callback)`: Register GET and POST routes
- `any(string $pattern, callable|array|string $callback)`: Register all HTTP method routes
- `group(string $prefix, array|string $middleware, callable $callback)`: Group routes
- `dispatch(string|null $method, string|null $uri)`: Dispatch the request
- `loadRoutes(string $file)`: Load routes from a file
- `compatible_mode(string $string)`: Enable compatible mode

### Route Class

#### Methods

- `middleware(array|string $middleware)`: Add middleware to the route

### Exceptions

- `NotFoundException`: Thrown when no route matches
- `InvalidCallbackException`: Thrown when callback is invalid

## Testing

Run tests with:

```bash
php test.php
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

sn01615
