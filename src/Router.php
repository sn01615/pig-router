<?php

namespace Pig\Router;

class Router
{
    private $routes = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];
    private $compatible = '';

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

    public function get_post($pattern, $callback)
    {
        $routers = [];
        foreach ([
                     'GET',
                     'POST',
                 ] as $method) {
            $routers[] = $this->addRoute($method, $pattern, $callback);
        }
        return $routers;
    }

    public function any($pattern, $callback)
    {
        $routers = [];
        foreach ([
                     'GET',
                     'POST',
                     'PUT',
                     'DELETE',
                     'PATCH',
                 ] as $method) {
            $routers[] = $this->addRoute($method, $pattern, $callback);
        }
        return $routers;
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
     * @param array|string $middleware
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
     * @throws \Pig\Router\NotFoundException
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
                if ($this->compatible && isset($_GET[$this->compatible])) {
                    $uri = $_GET[$this->compatible];
                }
                $uri = explode('?', $uri)[0];
            } else {
                $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : [];
                if (isset($argv[1])) {
                    $_uri = [''];
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
    }

    private function matchRoute($pattern, $uri)
    {
        // 转义特殊字符并构建安全的正则表达式
        $escapedPattern = preg_quote($pattern, '#');
        // 使用更精确的替换规则
        $regexPattern = '#^' . preg_replace('/\\\{([^\/]+?)\\\}/', '([^/]+)', $escapedPattern) . '$#';

        if (preg_match($regexPattern, $uri, $matches)) {
            // 提取参数名称
            preg_match_all('/\{([^\/}]+)}/', $pattern, $paramNames);
            $paramNamesList = $paramNames[1];

            $params = [];
            // 确保参数名称和匹配值对应正确
            for ($i = 1; $i < count($matches); $i++) {
                $paramName = isset($paramNamesList[$i - 1]) ? $paramNamesList[$i - 1] : "param$i";
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
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        } elseif (is_object($callback)) {
            // 对象实例
            if (method_exists($callback, 'handle')) {
                return call_user_func_array([$callback, 'handle'], $params);
            }
        }

        throw new InvalidCallbackException("Invalid callback");
    }

    /**
     * @throws \Pig\Router\NotFoundException
     */
    private function handleNotFound()
    {
        throw new NotFoundException("404 Not Found");
    }

    public function compatible_mode($string)
    {
        $this->compatible = $string;
    }

    public function loadRoutes($file)
    {
        $router = $this;
        include $file;
    }
}
