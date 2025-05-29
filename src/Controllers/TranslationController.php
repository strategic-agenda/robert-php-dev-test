<?php

namespace App\Controllers;

use App\Services\Translation\TranslationUnit;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TranslationController
{
    private array $translations = [];

    public function getAll(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode($this->translations));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getOne(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $translation = $this->findTranslation($id);

        if (!$translation) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(json_encode(['error' => 'Translation not found']));
        }

        $response->getBody()->write(json_encode($translation));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            $translation = new TranslationUnit(
                $data['source_text'],
                $data['source_language'],
                $data['target_language']
            );

            if (isset($data['target_text'])) {
                $translation->updateTargetText($data['target_text']);
            }

            $this->translations[] = $translation;

            $response->getBody()->write(json_encode($translation));
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\InvalidArgumentException $e) {
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(json_encode(['error' => $e->getMessage()]));
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $translation = $this->findTranslation($id);

        if (!$translation) {
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(json_encode(['error' => 'Translation not found']));
        }

        try {
            if (isset($data['target_text'])) {
                $translation->updateTargetText($data['target_text']);
            }

            $response->getBody()->write(json_encode($translation));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\InvalidArgumentException $e) {
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(json_encode(['error' => $e->getMessage()]));
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $index = $this->findTranslationIndex($id);

        if ($index === null) {
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(json_encode(['error' => 'Translation not found']));
        }

        array_splice($this->translations, $index, 1);
        return $response->withStatus(204);
    }

    private function findTranslation(string $id): ?TranslationUnit
    {
        foreach ($this->translations as $translation) {
            if ($translation->getId() === $id) {
                return $translation;
            }
        }
        return null;
    }

    private function findTranslationIndex(string $id): ?int
    {
        foreach ($this->translations as $index => $translation) {
            if ($translation->getId() === $id) {
                return $index;
            }
        }
        return null;
    }
} 