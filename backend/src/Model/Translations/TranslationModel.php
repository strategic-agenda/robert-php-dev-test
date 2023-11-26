<?php

declare(strict_types=1);

namespace Intobi\Model\Translations;

use Kernel\Model\ResourceModel\AbstractDB;

class TranslationModel extends AbstractDB
{
    const TABLE = 'translations';
    const PRIMARY_KEY_COLUMN = 'translation_id';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, self::PRIMARY_KEY_COLUMN);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return (int)$this->getData('translation_id');
    }
}