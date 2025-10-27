<?php

namespace Framework\Libs\DataBase;


use Framework\Libs\Exception\DataBaseException;
use ReflectionClass;
use Framework\Libs\Annotations\DataBase\Model;
use PDO;

/**
 * @template T 
*/
class Repository {
    private string $table;

    private QueryBuilder $queryBuilder;

    private PDO $pdo;

    public function __construct(string $classModel) {
        $this->table = "";
        $class = new ReflectionClass($classModel);
        if(!$this->isModel($class, $this->table)) throw new DataBaseException(
            "The class $classModel don't is a model"
        );

        $this->queryBuilder = new QueryBuilder($this->table);

    }

    public function run(): void {
        
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

    public function select(array $collumns): QueryBuilder {
        return $this->queryBuilder->select($collumns);
    }

    private function isModel(ReflectionClass $class, string &$table = ""): bool {
        $class_atributes = $class->getAttributes(Model::class);
        foreach($class_atributes as $attr) {
            if($attr->getName() == Model::class) {
                $table = $attr->newInstance()->table;
                return true;
            }
        }
        return false;
    }
}

