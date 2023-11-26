<?php

declare(strict_types=1);

namespace Intobi\Controller\Translate;

use Exception;
use Kernel\Http\Api\ControllerInterface;
use Kernel\Model\Exceptions\ValidateException;
use Intobi\Model\Translations\TranslationModel;
use Intobi\Model\Translations\TranslationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Save implements ControllerInterface
{
    /**
     * @var TranslationRepository
     */
    private TranslationRepository $translationRepository;

    public function __construct()
    {
        $this->translationRepository = new TranslationRepository();
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

            $translation = (new TranslationModel())->setData($data);

            $this->translationRepository->save($translation);

            return $response->setData([
                'data' => $translation
            ]);
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