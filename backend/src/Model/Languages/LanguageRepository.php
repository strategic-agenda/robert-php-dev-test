<?php

declare(strict_types=1);

namespace Intobi\Model\Languages;

use Kernel\Model\Exceptions\ValidateException;

class LanguageRepository
{
    /**
     * @var LanguageModel
     */
    private LanguageModel $model;

    public function __construct()
    {
        $this->model = new LanguageModel();
    }

    /**
     * @param string $languageCode
     * @return LanguageModelInterface|null
     */
    public function get(string $languageCode): ?LanguageModelInterface
    {
        $model = $this->model->load($languageCode);

        if ($model->isEmpty()) {
            return null;
        }

        return $model;
    }

    /**
     * @param LanguageModel $model
     * @return LanguageModelInterface
     * @throws ValidateException
     */
    public function save(LanguageModel $model): LanguageModelInterface
    {
        $this->validate($model);
        $model->save();

        return $model;
    }

    /**
     * @param array $params
     * @return LanguageModel[]
     */
    public function getList(array $params = []): array
    {
        $table = LanguageModel::TABLE;
        $sql = "SELECT * FROM $table ";

        $sql .= ' ORDER BY -language_code';

        return $this->model->selectRow($sql, $params);
    }

    /**
     * @param LanguageModel $model
     * @return void
     * @throws ValidateException
     */
    private function validate(LanguageModel $model): void
    {
        if (!$model->getLanguageCode() || !$model->getLanguageName()) {
            throw new ValidateException('Fields language code and language name required!');
        }

        if (strlen($model->getLanguageCode()) > 2 || strlen($model->getLanguageCode()) < 2){
            throw new ValidateException('The language code should be exactly 2 characters!');
        }
    }
}