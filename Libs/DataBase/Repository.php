<?php

namespace Framework\Libs\DataBase;

use Framework\Libs\Exception\DataBaseException;

use Framework\Libs\Annotations\DataBase\Model;
use Framework\Libs\DataBase\Query\DeleteBuilder;
use Framework\Libs\DataBase\Query\InsertBuilder;
use Framework\Libs\DataBase\Query\QueryBuilder;
use Framework\Libs\DataBase\Query\SelectBuilder;
use Framework\Libs\DataBase\Query\UpdateBuilder;

use ReflectionClass;


class Repository {
    /** @var string $table */
    private $table;

    /** @var QueryBuilder $queryBuilder */
    private $queryBuilder;

    /** @var Conection $conn */
    private $conn;

    /**
     * @param string $classModel
    */
    public function __construct($classModel) {
        $class = new ReflectionClass($classModel);
        if(!$this->isModel($class, $this->table)) throw new DataBaseException(
            "The class $classModel don't is a model"
        );

        $this->queryBuilder = new QueryBuilder($this->table, $this);
        $this->conn = new Conection();
    }

    /**
     * @param string $query
     * @return array
    */
    public function run($query) {
        return $this->conn->run($query);
    }

    /**
     * @param array $collumns
     * @return SelectBuilder
    */
    public function select(array $collumns): SelectBuilder {
        return $this->queryBuilder->select($collumns);
    }

    /**
     * @param array $collumns
     * @return InsertBuilder
    */
    public function insert(array $collumns): InsertBuilder {
        return $this->queryBuilder->insert($collumns);
    }

    /**
     * @param array $collumns
     * @return UpdateBuilder
    */
    public function update(array $collumns): UpdateBuilder {
        return $this->queryBuilder->update($collumns);
    }

    /**
     * @param array $collumns
     * @return DeleteBuilder
    */
    public function delete(array $collumns): DeleteBuilder {
        return $this->queryBuilder->delete($collumns);
    }

    /**
     * @param ReflectionClass $class
     * @param string $table
     * @return bool
    */
    private function isModel($class, &$table = "") {
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

