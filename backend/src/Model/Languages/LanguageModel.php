<?php

declare(strict_types=1);

namespace Intobi\Model\Languages;

use Kernel\Model\ResourceModel\AbstractDB;

class LanguageModel extends AbstractDB implements LanguageModelInterface
{
    const TABLE = 'languages';
    const PRIMARY_KEY_COLUMN = 'language_code';

    protected function _construct(): void
    {
        $this->_init(self::TABLE, self::PRIMARY_KEY_COLUMN);
    }

    /**
     * @inheritDoc
     */
    public function setLanguageCode(string $languageCode): LanguageModelInterface
    {
        $this->setData(self::LANGUAGE_CODE, $languageCode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLanguageName(string $languageName): LanguageModelInterface
    {
        $this->setData(self::LANGUAGE_NAME, $languageName);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLanguageCode(): ?string
    {
        return $this->getData(self::LANGUAGE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getLanguageName(): ?string
    {
        return $this->getData(self::LANGUAGE_NAME);
    }
}