<?php

declare(strict_types=1);

namespace Kernel\Model\ResourceModel;

use Kernel\DataObject;
use Kernel\DB\Connection;
use JsonSerializable;
use PDO;

abstract class AbstractDB extends DataObject implements JsonSerializable
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $primaryKey;

    public function __construct()
    {
        $this->pdo = Connection::getConnection();
        $this->_construct();
    }

    /**
     * @param string $table
     * @param string $primaryKey
     * @return void
     */
    protected function _init(string $table, string $primaryKey): void
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    /**
     * In this method you need call `_init` method when inherit this class
     * @return void
     */
    abstract protected function _construct(): void;

    /**
     * @param $value
     * @param $column
     * @return $this
     */
    public function load($value, $column = null): self
    {
        if ($column === null) {
            $column = $this->primaryKey;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE `' . $column . '` = :value');
        $stmt->bindParam(':value', $value);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->setData($data ?: []);

        return $this;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $primaryKey = $this->getData($this->primaryKey);
        $newModel = (new static())->load($primaryKey);

        if ($newModel->isEmpty()) {
            $createSql = 'INSERT INTO ' . $this->table .
                ' (' . $this->getColumnsByComma() . ') VALUES (' . $this->getQuestionMarkByComma() . ')';
            $stmt = $this->pdo->prepare($createSql);
            $stmt->execute(array_values($this->getData()));
        } else {
            $this->unsetData($this->primaryKey);
            $updateSql = 'UPDATE ' . $this->table . ' SET ' . $this->updateStatement() . ' WHERE ' . $this->primaryKey . ' = \'' . $primaryKey . '\'';
            $stmt = $this->pdo->prepare($updateSql);
            $stmt->execute(array_values($this->getData()));
            $this->load($primaryKey);
        }
    }

    /**
     * @return string
     */
    private function getColumnsByComma(): string
    {
        $array = [];

        $data = $this->getData();

        $count = sizeof($data);

        $i = 0;

        foreach ($data as $key => $value)
        {
            $i++;

            if ($i == $count) {
                $array[] = $key;
                break;
            }

            $array[] = $key . ',';
        }

        return implode('', $array);
    }

    /**
     * @return string
     */
    private function getQuestionMarkByComma(): string
    {
        $array = [];

        $data = $this->getData();

        $count = sizeof($data);

        $i = 0;

        foreach ($data as $key => $value)
        {
            $i++;

            if ($i == $count) {
                $array[] = '?';
                break;
            }

            $array[] = '?' . ',';
        }

        return implode('', $array);
    }

    /**
     * @return string
     */
    private function updateStatement(): string
    {
        $array = [];

        $data = $this->getData();

        $count = sizeof($data);

        $i = 0;

        foreach ($data as $key => $value)
        {
            $i++;

            if ($i == $count) {
                $array[] = $key . ' = ?';
                break;
            }

            $array[] = $key . ' = ?' . ',';
        }

        return implode('', $array);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->getData();
    }

    /**
     * @param $query
     * @param array $params
     * @return static[]
     */
    public function selectRow($query, array $params = [])
    {
        $stm = $this->pdo->prepare($query);
        $stm->execute($this->prepareParams($params));
        $items = $stm->fetchAll(PDO::FETCH_ASSOC);
        $items = array_map(fn ($item) => (new static())->setData($item), $items);

        return $items;
    }

    /**
     * @param array $params
     * @return array
     */
    private function prepareParams(array $params): array
    {
        $array = [];

        foreach ($params as $key => $value)
        {
            if (strpos($key, ':') !== false) {
                continue;
            }
            $array[":$key"] = $value;
        }

        return $array;
    }
}