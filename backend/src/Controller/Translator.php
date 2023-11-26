<?php

declare(strict_types=1);

namespace Intobi\Controller;

use Kernel\Http\Api\ControllerInterface;
use Kernel\Model\Exceptions\ValidateException;
use Intobi\Model\Languages\LanguageModel;
use Intobi\Model\Translations\TranslationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method
 */
class Translator implements ControllerInterface
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
        try {
            $data = $this->translationRepository->translate($request->query->all());
        } catch (ValidateException $validateException) {
            return $this->response(message: $validateException->getMessage());
        }

        if ($data->isEmpty()) {
            return $this->response(message: 'No result.');
        }

        $word = mb_strpos($data->getSourceText(), $request->get('search')) === false ? $data->getSourceText() : $data->getTranslatedText();
        $message = 'Translated from ' . (new LanguageModel())->load($data->getTargetLanguage())->getLanguageName();

        return $this->response(message: $message, result: $word, data: $data->toArray());
    }

    /**
     * @param string $message
     * @param string $result
     * @param array $data
     * @return JsonResponse
     */
    public function response(string $message = '', string $result = '', array $data = []): JsonResponse
    {
        $response = new JsonResponse();
        return $response->setData([
            'data' => $data,
            'message' => $message,
            'result' => $result
        ]);
    }
}