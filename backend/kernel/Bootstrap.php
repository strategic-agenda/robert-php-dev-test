<?php

declare(strict_types=1);

namespace Kernel;

use Exception;
use Kernel\Http\Exceptions\RouterException;
use Symfony\Component\HttpFoundation\Request;
use Kernel\Http\Router;

class Bootstrap
{
    public static function run(): void
    {
        try {
            $request = Request::createFromGlobals();
            $output = Router::start($request);
            $output->send();
        }
        catch (RouterException $routerException)
        {
            $routerException->terminate();
        }
        catch (Exception $e)
        {
            if ($_ENV['DEBUG'] === true) {
                print $e->getMessage() . "\n";
                print $e->getTraceAsString();
            }
        }
    }
}