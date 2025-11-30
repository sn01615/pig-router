<?php
namespace Pig\Router;

class Route
{
    private $method;
    private $pattern;
    private $callback;
    private $middleware = [];
    private $name;
    
    public function __construct($method, $pattern, $callback)
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->callback = $callback;
    }
    
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }
    
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getPattern()
    {
        return $this->pattern;
    }
    
    public function getCallback()
    {
        return $this->callback;
    }
    
    public function getMiddleware()
    {
        return $this->middleware;
    }
    
    public function getName()
    {
        return $this->name;
    }
}
