<?php

namespace tests;

require_once '/var/www/html/configs/env.php';

use PHPUnit\Framework\TestCase;
use Intobi\Controller\Language\Save;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageSaveControllerUnitTest extends TestCase
{
    public function testExecuteWithValidData()
    {
        $controller = new Save();
        $request = new Request([], ['language_code' => 'EN', 'language_name' => 'English']);

        $response = $controller->execute($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testExecuteWithValidOnlyLanguageCode()
    {
        $controller = new Save();
        $request = new Request([], ['language_code' => 'EN']);

        $response = $controller->execute($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testExecuteWithValidOnlyLanguageName()
    {
        $controller = new Save();
        $request = new Request([], ['language_name' => 'English']);

        $response = $controller->execute($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testExecuteWithEmptyFields()
    {
        $controller = new Save();
        $request = new Request([], []);

        $response = $controller->execute($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
