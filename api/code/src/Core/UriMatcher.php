<?php

namespace Kirilmaz\Interview\Core;

class UriMatcher {
    protected UriParser $urlParser;
    protected Router $router;
    protected string $route;
    protected string $controller;
    protected object $variables;

    function __construct(Router $router, UriParser $urlParser) {
        $this->urlParser = $urlParser;
        $this->router = $router;
        $this->variables = (object)[];
    }

    public function variables(): object | null {
        return $this->variables;
    }

    public function controller(): string | null {
        return $this->controller ?? null;
    }

    public function match() {
        foreach ($this->router->getRoutes() as $route) {
            if (count($route->parts) === count($this->urlParser->parts())) {
                $difference = array_diff($this->urlParser->parts(), $route->parts);

                if(empty($difference)) {
                    $this->route = $route->route;
                    $this->controller = $route->controller;
                    return;
                }

                if (count($difference) === count($route->variables)) {
                    $this->compare($route, $this->urlParser->parts(), array_values($difference));
                }
            }
        }
    }

    private function compare($route, $urlArray, $difference): void {
        $routeArray = array_reverse($route->parts);
        $urlArray = array_reverse($urlArray);

        if (empty(array_diff($routeArray, $urlArray)) && $route->method === $this->urlParser->getHttpMethod()) {
            $this->route = $route->route;
            $this->controller = $route->controller;
            return;
        }

        for ($i = 0; $i < count($difference); $i++) {
            unset($routeArray[$i]);
            unset($urlArray[$i]);
        }

        if (empty(array_diff(array_reverse($routeArray), array_reverse($urlArray))) && ($route->method === $this->urlParser->getHttpMethod())) {
            $this->route = $route->route;
            $this->controller = $route->controller;

            $i = 0;
            foreach ($route->variables as $key) {
                $this->variables->{$key} = $difference[$i];
                $i++;
            }
        }
    }
}
