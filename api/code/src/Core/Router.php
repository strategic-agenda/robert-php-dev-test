<?php

namespace Kirilmaz\Interview\Core;

use Kirilmaz\Interview\Core\RouteParser;

class Router {
    protected array $allowedMethods;
    protected Array $routes;

    public function __construct() {
        $this->allowedMethods = [
            'get', 'post', 'put', 'delete'
        ];

        $this->routes = [
            (object) [
                'route' => '/',
                'parts' => [],
                'method' => 'get',
                'variables' => [],
                'controller' => 'PublicController'
            ]
        ];
    }

    public function add(string $route, string $method, string $controller): void {
        $parser = new RouteParser($route);

        $this->routes[] = (object)[
            'route' => $parser->route(),
            'parts' => array_filter(explode('/', $route)),
            'method' => $method,
            'variables' => $parser->variables(),
            'controller' => $controller
        ];
    }

    public function getRoutes(): array {
        return $this->routes;
    }
}
