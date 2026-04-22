<?php

namespace Pig\Router\Tests;

use Pig\Router\Router;
use Pig\Router\Route;
use Pig\Router\NotFoundException;
use Pig\Router\InvalidCallbackException;
use Pig\Router\MethodNotFoundException;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /** @var Router */
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testGetRoute()
    {
        $route = $this->router->get('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('GET', $route->getMethod());
        $this->assertEquals('/test', $route->getPattern());
    }

    public function testPostRoute()
    {
        $route = $this->router->post('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('POST', $route->getMethod());
    }

    public function testPutRoute()
    {
        $route = $this->router->put('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('PUT', $route->getMethod());
    }

    public function testDeleteRoute()
    {
        $route = $this->router->delete('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('DELETE', $route->getMethod());
    }

    public function testPatchRoute()
    {
        $route = $this->router->patch('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('PATCH', $route->getMethod());
    }

    public function testHeadRoute()
    {
        $route = $this->router->head('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('HEAD', $route->getMethod());
    }

    public function testOptionsRoute()
    {
        $route = $this->router->options('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('OPTIONS', $route->getMethod());
    }

    public function testGetPostRoute()
    {
        $route = $this->router->get_post('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('GET|POST', $route->getMethod());
    }

    public function testAnyRoute()
    {
        $route = $this->router->any('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('*', $route->getMethod());
    }

    public function testRouteDispatch()
    {
        $this->router->get('/test', function() {
            return 'success';
        });

        $result = $this->router->dispatch('GET', '/test');
        $this->assertEquals('success', $result);
    }

    public function testRouteWithParameters()
    {
        $this->router->get('/user/{id}', function($id) {
            return 'user_' . $id;
        });

        $result = $this->router->dispatch('GET', '/user/123');
        $this->assertEquals('user_123', $result);
    }

    public function testRouteWithMultipleParameters()
    {
        $this->router->get('/user/{id}/post/{postId}', function($id, $postId) {
            return 'user_' . $id . '_post_' . $postId;
        });

        $result = $this->router->dispatch('GET', '/user/123/post/456');
        $this->assertEquals('user_123_post_456', $result);
    }

    public function testRouteNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->router->dispatch('GET', '/nonexistent');
    }

    public function testMethodNotAllowed()
    {
        $this->router->get('/test', function() {
            return 'test';
        });

        $this->expectException(NotFoundException::class);
        $this->router->dispatch('POST', '/test');
    }

    public function testGroupRoutes()
    {
        $this->router->group('/api', [], function($router) {
            $router->get('/users', function() {
                return 'users';
            });
            $router->post('/users', function() {
                return 'create_user';
            });
        });

        $result1 = $this->router->dispatch('GET', '/api/users');
        $this->assertEquals('users', $result1);

        $result2 = $this->router->dispatch('POST', '/api/users');
        $this->assertEquals('create_user', $result2);
    }

    public function testMiddleware()
    {
        $middlewareExecuted = false;

        $this->router->get('/test', function() {
            return 'success';
        })->middleware(function() use (&$middlewareExecuted) {
            $middlewareExecuted = true;
            return true;
        });

        $result = $this->router->dispatch('GET', '/test');
        $this->assertEquals('success', $result);
        $this->assertTrue($middlewareExecuted);
    }

    public function testMiddlewareBlocksRequest()
    {
        $this->router->get('/test', function() {
            return 'success';
        })->middleware(function() {
            return false; // Block the request
        });

        $result = $this->router->dispatch('GET', '/test');
        $this->assertNull($result);
    }

    public function testBeforeMiddleware()
    {
        $beforeExecuted = false;

        $this->router->before(function() use (&$beforeExecuted) {
            $beforeExecuted = true;
            return true;
        });

        $this->router->get('/test', function() {
            return 'success';
        });

        $result = $this->router->dispatch('GET', '/test');
        $this->assertEquals('success', $result);
        $this->assertTrue($beforeExecuted);
    }

    public function testAfterMiddleware()
    {
        $afterExecuted = false;

        $this->router->after(function() use (&$afterExecuted) {
            $afterExecuted = true;
            return true;
        });

        $this->router->get('/test', function() {
            return 'success';
        });

        $result = $this->router->dispatch('GET', '/test');
        $this->assertEquals('success', $result);
        $this->assertTrue($afterExecuted);
    }

    public function testControllerCallback()
    {
        // Mock controller class
        eval('
            class MockController {
                public function index() {
                    return "controller_response";
                }
            }
        ');

        $this->router->get('/test', ['MockController', 'index']);

        $result = $this->router->dispatch('GET', '/test');
        $this->assertEquals('controller_response', $result);
    }

    public function testControllerStringCallback()
    {
        // Mock controller class
        eval('
            class MockController2 {
                public function show() {
                    return "string_controller_response";
                }
            }
        ');

        $this->router->get('/test', 'MockController2@show');

        $result = $this->router->dispatch('GET', '/test');
        $this->assertEquals('string_controller_response', $result);
    }

    public function testInvalidController()
    {
        $this->expectException(InvalidCallbackException::class);
        $this->router->get('/test', ['NonExistentController', 'method']);
        $this->router->dispatch('GET', '/test');
    }

    public function testInvalidMethod()
    {
        // Mock controller class without the method
        eval('
            class MockController3 {
                // No show method
            }
        ');

        $this->expectException(MethodNotFoundException::class);
        $this->router->get('/test', ['MockController3', 'show']);
        $this->router->dispatch('GET', '/test');
    }

    public function testCompatibleMode()
    {
        $this->router->compatible_mode('r');
        $this->router->get('/test', function() {
            return 'compatible';
        });

        // Simulate $_GET['r'] = '/test' and REQUEST_URI
        $_GET['r'] = '/test';
        $_SERVER['REQUEST_URI'] = '/some/other/path'; // This should be ignored when compatible mode is set

        $result = $this->router->dispatch('GET', null);
        $this->assertEquals('compatible', $result);

        unset($_GET['r'], $_SERVER['REQUEST_URI']);
    }

    public function testCliDispatch()
    {
        $this->router->get('/cli', function() {
            return 'cli_response';
        });

        // Simulate CLI arguments
        $_SERVER['argv'] = ['script.php', 'cli'];

        $result = $this->router->dispatch('GET', null);
        $this->assertEquals('cli_response', $result);

        unset($_SERVER['argv']);
    }
}
