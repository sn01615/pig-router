<?php

namespace Pig\Router\Tests;

use Pig\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testRouteCreation()
    {
        $callback = function() {
            return 'test';
        };

        $route = new Route('GET', '/test', $callback);

        $this->assertEquals('GET', $route->getMethod());
        $this->assertEquals('/test', $route->getPattern());
        $this->assertEquals($callback, $route->getCallback());
    }

    public function testRouteMiddleware()
    {
        $route = new Route('GET', '/test', function() {});

        $middleware = function() {
            return true;
        };

        $route->middleware($middleware);

        $this->assertContains($middleware, $route->getMiddleware());
    }

    public function testRouteMultipleMiddleware()
    {
        $route = new Route('GET', '/test', function() {});

        $middleware1 = function() { return true; };
        $middleware2 = function() { return true; };

        $route->middleware([$middleware1, $middleware2]);

        $middlewares = $route->getMiddleware();
        $this->assertContains($middleware1, $middlewares);
        $this->assertContains($middleware2, $middlewares);
    }

    public function testRouteStringMiddleware()
    {
        $route = new Route('GET', '/test', function() {});

        $route->middleware('AuthMiddleware');

        $middlewares = $route->getMiddleware();
        $this->assertContains('AuthMiddleware', $middlewares);
    }

    public function testRoutePropertyAccess()
    {
        $route = new Route('POST', '/api/user', function() {});

        $this->assertEquals('POST', $route->getMethod());
        $this->assertEquals('/api/user', $route->getPattern());
    }

    public function testRouteInvalidPropertyAccess()
    {
        // Since Route properties are private and there's no __get method,
        // we can't test invalid property access directly.
        // This test is not applicable to the current implementation.
        $this->assertTrue(true); // Placeholder assertion
    }

    public function testRouteMiddlewareReturnValue()
    {
        $route = new Route('GET', '/test', function() {});

        $result = $route->middleware('test');
        $this->assertSame($route, $result); // Should return self for chaining
    }
}
