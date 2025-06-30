<?php

namespace Kirilmaz\Interview\Traits;


trait QueryTrait {
    /**
     * @throws \Exception
     */
    public function query($sql): static {
        try {
            $this->_query = $this->_pdo->query($sql);
            return $this;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function prepare($sql): static {
        try {
            $this->_query = $this->_pdo->prepare($sql);
            return $this;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function execute($variables = []): static {
        if($this->_query) {
            try {
                $this->_query->execute($variables);
                return $this;
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        throw new \Exception('Query not set');
    }

    /**
     * @throws \Exception
     */
    public function all($type = \PDO::FETCH_OBJ): array | string {
        if($this->_query) {
            try {
                return $this->_query->fetchAll($type);
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        throw new \Exception('Query not set');
    }

    /**
     * @throws \Exception
     */
    public function first($type = \PDO::FETCH_OBJ): object | false {
        if($this->_query) {
            try {
                $result = $this->_query->fetch($type);
                return $result === false ? (object) [] : $result;
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        throw new \Exception('Query not set');
    }

    public function pdo(): \Kirilmaz\Interview\Core\Pdo {
        return $this->_pdo;
    }
}
