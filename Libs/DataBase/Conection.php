<?php

namespace Framework\Libs\DataBase;


use Framework\Libs\Exception\DataBaseException;
use ReflectionClass;
use Framework\Libs\Annotations\DataBase\Model;
use PDO;

class Conection {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = new PDO(
            "","",""
        );
    }

    public function startTransaction(): void {
        $this->pdo->beginTransaction();
    }

    public function commit(): void {
        $this->pdo->commit();
    }

    public function rollback(): void {
        $this->pdo->rollBack();
    }
}