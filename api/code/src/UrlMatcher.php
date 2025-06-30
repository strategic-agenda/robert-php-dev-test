<?php

namespace Kirilmaz\Interview;

use Kirilmaz\Interview\Core\Router;

class UrlMatcher {
    protected array $routes;

    public function __construct(Router $router) {
        $this->routes = $router->getRoutes();
    }

    public function match (string $route) {
        pd([$this->routes, $route]);
    }
}
