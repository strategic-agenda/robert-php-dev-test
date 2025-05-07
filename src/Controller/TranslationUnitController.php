<?php

namespace App\Controller;

use App\Model\TranslationUnit;
use App\Repository\TranslationUnitRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TranslationUnitController
{
    private TranslationUnitRepository $repository;

    public function __construct(TranslationUnitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function list(Request $request, Response $response): Response
    {
        $limit = (int) ($request->getQueryParams()['limit'] ?? 10);
        $offset = (int) ($request->getQueryParams()['offset'] ?? 0);

        $units = $this->repository->findAll($limit, $offset);
        $data = array_map(fn(TranslationUnit $unit) => $unit->toArray(), $units);

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $unit = $this->repository->findById((int) $args['id']);
        
        if (!$unit) {
            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode($unit->toArray()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $unit = new TranslationUnit(
            $data['sourceText'],
            $data['targetText'],
            $data['sourceLanguage'],
            $data['targetLanguage']
        );

        $id = $this->repository->create($unit);
        
        $response->getBody()->write(json_encode(['id' => $id]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $unit = $this->repository->findById((int) $args['id']);
        
        if (!$unit) {
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        $unit->updateTranslation($data['targetText']);
        
        $this->repository->update($unit);
        
        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        // Implementation for delete operation
        return $response->withStatus(204);
    }
} 