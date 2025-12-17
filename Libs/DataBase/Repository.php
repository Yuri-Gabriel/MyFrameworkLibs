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

    public function select(array $collumns): SelectBuilder {
        return $this->queryBuilder->select($collumns);
    }

    public function insert(array $collumns): InsertBuilder {
        return $this->queryBuilder->insert($collumns);
    }

    public function update(array $collumns): UpdateBuilder {
        return $this->queryBuilder->update($collumns);
    }

    public function delete(array $collumns): DeleteBuilder {
        return $this->queryBuilder->delete($collumns);
    }

    public function __call($name, $arguments) {
        if (method_exists($this, $name) && in_array($name, ["select", "insert", "update", "delete"])) {
            
            $result = call_user_func_array([$this, $name], $arguments);
            echo $result;
            return $result;
        }
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

