<?php

declare(strict_types=1);

namespace Kernel\Http;

use Kernel\Http\Api\ControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Kernel\Http\Exceptions\RouterException;
use Exception;

class Router
{
    public static array $routes = [];

    /**
     * @param string $url
     * @param string $class
     * @return void
     */
    public static function get(string $url, string $class): void
    {
        self::setRoute($url, $class);
    }

    /**
     * @param string $url
     * @param string $class
     * @return void
     */
    public static function post(string $url, string $class): void
    {
        self::setRoute($url, $class, Request::METHOD_POST);
    }

    /**
     * @param string $url
     * @param string $class
     * @return void
     */
    public static function put(string $url, string $class): void
    {
        self::setRoute($url, $class, Request::METHOD_PUT);
    }

    /**
     * @param string $url
     * @param string $class
     * @return void
     */
    public static function delete(string $url, string $class): void
    {
        self::setRoute($url, $class, Request::METHOD_DELETE);
    }

    /**
     * @param string $url
     * @param string $class
     * @return void
     */
    public static function options(string $url, string $class): void
    {
        self::setRoute($url, $class, Request::METHOD_OPTIONS);
    }

    /**
     * @param string $url
     * @param string $class
     * @param string $method
     * @return void
     */
    public static function setRoute(string $url, string $class, string $method = Request::METHOD_GET): void
    {
        self::$routes[$url . '__' . strtolower($method)] = [
            'class' => $class
        ];
    }

    /**
     * @param string $url
     * @param string $method
     * @return array|null
     */
    public static function getRoute(string $url, string $method = Request::METHOD_GET): ?array
    {
        return self::$routes[$url . '__' . strtolower($method)] ?? null;
    }

    /**
     * @throws RouterException|Exception
     */
    public static function start(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $url = $request->getPathInfo();
        $method = $request->getMethod();
        $route = self::getRoute($url, $method);

        if ($route === null) {
            throw new RouterException(sprintf('Route with url: %s not found and method %s.', $url, $method));
        }

        $controllerClassPath = $route['class'];
        $controller = new $controllerClassPath;

        if (!$controller instanceof ControllerInterface) {
            throw new Exception('Controller should implement ControllerInterface');
        }

        $result = $controller->execute($request);

        if (!$result instanceof Response) {
            throw new Exception('Execute method should return instance of Symfony\Component\HttpFoundation\Response');
        }

        return $result;
    }
}