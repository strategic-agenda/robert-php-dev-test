<?php

declare(strict_types=1);

namespace Kernel\Http\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface ControllerInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request): Response;
}