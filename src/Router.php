<?php

namespace Pig\Router;

class Router
{
    private $routes = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];
    private $namedRoutes = [];

    public function get($pattern, $callback)
    {
        return $this->addRoute('GET', $pattern, $callback);
    }

    public function post($pattern, $callback)
    {
        return $this->addRoute('POST', $pattern, $callback);
    }

    public function put($pattern, $callback)
    {
        return $this->addRoute('PUT', $pattern, $callback);
    }

    public function delete($pattern, $callback)
    {
        return $this->addRoute('DELETE', $pattern, $callback);
    }

    public function patch($pattern, $callback)
    {
        return $this->addRoute('PATCH', $pattern, $callback);
    }

    private function addRoute($method, $pattern, $callback)
    {
        $fullPattern = $this->groupPrefix . $pattern;
        $route = new Route($method, $fullPattern, $callback);

        if (!empty($this->groupMiddleware)) {
            $route->middleware($this->groupMiddleware);
        }

        $this->routes[] = $route;
        return $route;
    }

    /**
     * @param string $prefix
     * @param array $middleware
     * @param callable|string $callback
     * @return void
     */
    public function group($prefix, $middleware, $callback)
    {
        $oldPrefix = $this->groupPrefix;
        $oldMiddleware = $this->groupMiddleware;

        $this->groupPrefix .= $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, is_array($middleware) ? $middleware : [$middleware]);

        call_user_func($callback, $this);

        $this->groupPrefix = $oldPrefix;
        $this->groupMiddleware = $oldMiddleware;
    }

    /**
     * @throws \Exception
     */
    public function dispatch($method = null, $uri = null)
    {
        if ($method === null) {
            $method = empty($_SERVER['REQUEST_METHOD']) ? 'GET' : $_SERVER['REQUEST_METHOD'];
        }

        if ($uri === null) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $uri = $_SERVER['REQUEST_URI'];
                $uri = parse_url($uri, PHP_URL_PATH);
            } else {
                $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
                if (isset($argv[1])) {
                    $_uri = [];
                    foreach ($argv as $key => $_argv) {
                        if ($key >= 1) $_uri[] = $_argv;
                    }
                    $uri = implode('/', $_uri);
                } else {
                    $uri = '/';
                }
            }
        }

        /** @var Route $route */
        foreach ($this->routes as $route) {
            if ($route->getMethod() !== $method) {
                continue;
            }

            $pattern = $route->getPattern();
            $callback = $route->getCallback();

            // 处理路由参数
            $params = $this->matchRoute($pattern, $uri);
            if ($params !== false) {
                // 执行中间件
                $middleware = $route->getMiddleware();
                foreach ($middleware as $mw) {
                    if (!$this->executeMiddleware($mw)) {
                        return null;
                    }
                }

                // 执行回调函数
                return $this->executeCallback($callback, $params);
            }
        }

        // 未找到路由
        $this->handleNotFound();
        return null;
    }

    private function matchRoute($pattern, $uri)
    {
        // 将路由模式转换为正则表达式
        $pattern = preg_replace('/\{([^}]+)}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // 提取参数名
            preg_match_all('/\{([^}]+)}/', $pattern, $paramNames);
            $params = [];

            for ($i = 1; $i < count($matches); $i++) {
                $paramName = isset($paramNames[1][$i - 1]) ? $paramNames[1][$i - 1] : "param$i";
                $params[$paramName] = $matches[$i];
            }

            return $params;
        }

        return false;
    }

    private function executeMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            return call_user_func($middleware) !== false;
        }

        if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                return $instance->handle() !== false;
            }
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    private function executeCallback($callback, $params)
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_string($callback)) {
            // 控制器@方法 格式
            if (strpos($callback, '@') !== false) {
                list($controller, $method) = explode('@', $callback);
                if (class_exists($controller)) {
                    $instance = new $controller();
                    if (method_exists($instance, $method)) {
                        return call_user_func_array([$instance, $method], $params);
                    }
                    throw new \Exception("Method does not exist");
                }
            }
        } elseif (is_array($callback)) {
            // [控制器, 方法] 形式
            if (count($callback) == 2 && is_string($callback[0]) && is_string($callback[1])) {
                if (class_exists($callback[0])) {
                    $instance = new $callback[0]();
                    if (method_exists($instance, $callback[1])) {
                        return call_user_func_array([$instance, $callback[1]], $params);
                    }
                    throw new \Exception("Method does not exist");
                }
            }
        } elseif (is_object($callback)) {
            // 对象实例
            if (method_exists($callback, 'handle')) {
                return call_user_func_array([$callback, 'handle'], $params);
            }
        }

        throw new \Exception("Invalid callback");
    }

    private function handleNotFound()
    {
        http_response_code(404);
        echo "404 Not Found";
    }

    public function url($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            return null;
        }

        $pattern = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $pattern = str_replace("{{$key}}", $value, $pattern);
        }

        return $pattern;
    }
}
