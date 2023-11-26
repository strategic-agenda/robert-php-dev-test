<?php

declare(strict_types=1);

namespace Intobi\Model\Translations;

use Kernel\Model\Exceptions\ValidateException;

class TranslationRepository
{
    /**
     * @var TranslationModel
     */
    private TranslationModel $model;

    /**
     * @var array
     */
    private array $params = [];

    public function __construct()
    {
        $this->model = new TranslationModel();
    }

    /**
     * @param int $id
     * @return TranslationModel|null
     */
    public function getById(int $id): ?TranslationModel
    {
        $model = $this->model->load($id);

        if ($model->isEmpty()) {
            return null;
        }

        return $model;
    }

    /**
     * @param TranslationModel $model
     * @return TranslationModel
     * @throws ValidateException
     */
    public function save(TranslationModel $model): TranslationModel
    {
        $this->validate($model);
        $model->save();

        return $model;
    }

    /**
     * @return TranslationModel[]
     */
    public function getList(): array
    {
        $table = TranslationModel::TABLE;
        $sql = "SELECT * FROM {$table} ";
        $sql .= $this->buildWhereClause();
        $sql .= ' ORDER BY -translation_id';
        $params = $this->getParams();
        return $this->model->selectRow($sql, $params);
    }

    /**
     * @return string
     */
    private function buildWhereClause(): string
    {
        $conditions = [];
        if (!empty($this->params['source_language']) && !empty($this->params['target_language'])) {
            $conditions[] = 'source_language = :source_language AND target_language = :target_language';
        } else {
            unset($this->params['source_language']);
            unset($this->params['target_language']);
        }

        if (!empty($this->params['search'])) {
            $conditions[] = '(translated_text LIKE :search OR source_text LIKE :search)';
            $this->params['search'] = '%' . $this->params['search'] . '%';
        } else {
            unset($this->params['search']);
        }


        if ($conditions) {
            return 'WHERE ' . implode(' AND ', $conditions);
        } else {
            $this->params = [];
            return '';
        }
    }


    /**
     * @param TranslationModel $model
     * @return void
     * @throws ValidateException
     */
    private function validate(TranslationModel $model): void
    {
        if (!$model->getSourceLanguage() || !$model->getTargetLanguage() || !$model->getSourceText() || !$model->getTranslatedText()) {
            throw new ValidateException('All fields are required!');
        }

        if ($model->getSourceLanguage() == $model->getTargetLanguage()) {
            throw new ValidateException('Field \'source language\' and \'target language\' connot be the same!');
        }
    }

    /**
     * @param array $params
     * @return TranslationModel
     * @throws ValidateException
     */
    public function translate(array $params): TranslationModel
    {
        if (empty($params['source_language'])) {
            $autoDetect = true;
        }

        if (empty($params['target_language'])) {
            throw new ValidateException('Language on which translate is required!');
        }

        $code = $params['target_language'];

        $search = $params['search'] ?: null;
        $sourceLanguage = $params['source_language'] ?? null;

        if (!$search) {
            throw new ValidateException('Enter you word.');
        }

        if ($sourceLanguage == $code) {
            throw new ValidateException('Please choose different languages.');
        }

        $table = TranslationModel::TABLE;

        $sql = "SELECT * FROM $table ";

        if ($autoDetect) {
            $sql .= "WHERE (target_language = :target_language OR source_language = :target_language) AND (source_text LIKE :search OR translated_text LIKE :search)";
            $sqlParams = [
                'target_language' => $code,
                'search' => "%$search%",
            ];
        } else {
            $sql .= "WHERE ((target_language = :target_language AND source_language = :source_language) OR (target_language = :source_language AND source_language = :target_language)) ";
            $sql .= "AND (source_text LIKE :search OR translated_text LIKE :search)";
            $sqlParams = [
                'target_language' => $code,
                'source_language' => $sourceLanguage,
                'search' => "%$search%",
            ];
        }

        $sql .= " ORDER BY -translation_id LIMIT 1";

        $model = $this->model->selectRow($sql, $sqlParams);

        return $model[0] ?? new TranslationModel();
    }

    /**
     * @return void
     */
    private function prepareParams(): void
    {
        foreach (['source_language', 'target_language', 'search'] as $param) {
            if (empty($this->params[$param])) {
                unset($this->params[$param]);
            }
        }
    }

    /**
     * @return array
     */
    private function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return self
     */
    public function setParams(array $params = []): self
    {
        $this->params = $params;
        return $this;
    }
}