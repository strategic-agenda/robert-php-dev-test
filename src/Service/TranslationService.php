<?php

namespace CAT\Service;

use CAT\Repository\TranslationUnitRepository;
use CAT\Repository\TranslationRepository;

class TranslationService
{
    private $repository;
    private $translationRepository;

    public function __construct(TranslationUnitRepository $repository, TranslationRepository $translationRepository)
    {
        $this->repository = $repository;
        $this->translationRepository = $translationRepository;
    }

    public function createTranslationUnit(string $sourceText, int $sourceLanguageId): array
    {
        return $this->repository->create($sourceText, $sourceLanguageId);
    }

    public function getTranslationUnit(int $unitId): ?array
    {
        return $this->repository->find($unitId);
    }

    public function updateTranslationUnit(int $unitId, string $sourceText): ?array
    {
        return $this->repository->update($unitId, $sourceText);
    }

    public function deleteTranslationUnit(int $unitId): bool
    {
        return $this->repository->delete($unitId);
    }

    public function listTranslationUnits(int $page = 1, int $perPage = 50): array
    {
        return $this->repository->findAll($page, $perPage);
    }

    public function createTranslation(int $unitId, int $languageId, string $translatedText): array
    {
        return $this->translationRepository->create($unitId, $languageId, $translatedText);
    }

    public function getTranslation(int $translationId): ?array
    {
        return $this->translationRepository->find($translationId);
    }

    public function updateTranslation(int $translationId, string $translatedText): ?array
    {
        return $this->translationRepository->update($translationId, $translatedText);
    }

    public function deleteTranslation(int $translationId): bool
    {
        return $this->translationRepository->delete($translationId);
    }

    public function listTranslationsForUnit(int $unitId): array
    {
        return $this->translationRepository->findByUnit($unitId);
    }
}
