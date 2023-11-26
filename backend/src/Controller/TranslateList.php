<?php

declare(strict_types=1);

namespace Intobi\Controller;

use Kernel\Http\Api\ControllerInterface;
use Intobi\Model\Translations\TranslationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslateList implements ControllerInterface
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
        $items = $this->translationRepository->setParams($request->query->all())->getList();

        return (new JsonResponse())->setData([
            'items' => $items
        ]);
    }
}