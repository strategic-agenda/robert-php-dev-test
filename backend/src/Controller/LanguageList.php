<?php

declare(strict_types=1);

namespace Intobi\Controller;

use Kernel\Http\Api\ControllerInterface;
use Intobi\Model\Languages\LanguageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LanguageList implements ControllerInterface
{
    private LanguageRepository $languageRepository;

    public function __construct()
    {
        $this->languageRepository = new LanguageRepository();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function execute(Request $request): JsonResponse
    {
        $model = $this->languageRepository->getList();

        return (new JsonResponse())->setData(['items' => $model]);
    }
}