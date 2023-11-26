<?php

declare(strict_types=1);

namespace Kernel\Http\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RouterException extends \Exception
{
    /**
     * @return void
     */
    public function terminate()
    {
        $json = new JsonResponse();

        $json->setData([
            'error' => $this->getMessage()
        ]);

        $json->setStatusCode(Response::HTTP_NOT_FOUND);
        $json->send();
    }
}