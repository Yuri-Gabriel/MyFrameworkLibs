<?php

namespace Framework\Libs\DataBase;

use Exception;
use Framework\Libs\Exception\DataBaseException;
use ReflectionClass;
use Framework\Libs\Annotations\DataBase\Model;

use PDO;
use PDOException;

class Repository {
    /** @var string $table */
    private $table;

    /** @var QueryBuilder $queryBuilder */
    private $queryBuilder;

    /** @var PDO $pdo */
    private $pdo;

    /**
     * @param string $classModel
    */
    public function __construct($classModel) {
        $class = new ReflectionClass($classModel);
        if(!$this->isModel($class, $this->table)) throw new DataBaseException(
            "The class $classModel don't is a model"
        );

        $this->table = "";
        $this->queryBuilder = new QueryBuilder($this->table, $this);
        try {
            $this->pdo = new PDO("", "", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $errPDO) {

        } catch (Exception $err) {
            
        }
        

    }

    /**
     * @param string $query
     * @return array
    */
    public function run($query) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

