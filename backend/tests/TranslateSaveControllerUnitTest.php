<?php


require_once '/var/www/html/configs/env.php';

use PHPUnit\Framework\TestCase;
use Intobi\Controller\Translate\Save;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslateSaveControllerUnitTest extends TestCase
{
    public function testExecuteWithValidData()
    {
        $controller = new Save();
        $request = new Request([], [
            'source_language' => 'EN',
            'target_language' => 'UA',
            'source_text' => 'Test',
            'translated_text' => 'Тест',
        ]);

        $response = $controller->execute($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testExecuteWithValidOnlyTargetLanguage()
    {
        $controller = new Save();
        $request = new Request([], [
            'target_language' => 'UA'
        ]);

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
