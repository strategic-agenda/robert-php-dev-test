<?php

declare(strict_types=1);

namespace Intobi\Controller\Language;

use Kernel\Http\Api\ControllerInterface;
use Kernel\Model\Exceptions\ValidateException;
use Exception;
use Intobi\Model\Languages\LanguageModel;
use Intobi\Model\Languages\LanguageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Save implements ControllerInterface
{
    /**
     * @var LanguageRepository
     */
    private LanguageRepository $languageRepository;

    public function __construct()
    {
        $this->languageRepository = new LanguageRepository();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request): Response
    {
        $response = new JsonResponse();

        try {
            $data = $request->request->all();

            $translation = (new LanguageModel())->setData($data);

            $this->languageRepository->save($translation);

            $response->setData([
                'data' => $translation
            ]);

            return $response;

        } catch (ValidateException $validateException) {
            return $response
                ->setData([
                    'message' => $validateException->getMessage()
                ])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return $response
                ->setData([
                    'message' => $e->getMessage()
                ])
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}