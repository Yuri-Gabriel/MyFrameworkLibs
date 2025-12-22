<?php

namespace Framework\Libs\DataBase;

class QueryBuilder {

    /** @var string $table */
    private $table;

    /** @var Repository $repository */
    private $repository;

    /**
     * @param string $table
     * @param Repository $repository
    */
    public function __construct($table, $repository) {
        $this->table = $table;
        $this->repository = $repository;
    }

    /**
     * @param array $collumns
     * @return SelectBuilder
    */
    public function select(array $columns): SelectBuilder {
        return (new SelectBuilder($this->table, $this->repository))->select($columns);
    }

    /**
     * @param array $collumns
     * @return InsertBuilder
    */
    public function insert(array $collumns): InsertBuilder  {
        return (new InsertBuilder($this->table, $this->repository))->insert($collumns);
    }

    /**
     * @param array $collumns
     * @return UpdateBuilder
    */
    public function update(array $collumns): UpdateBuilder  {
        return (new UpdateBuilder($this->table, $this->repository))->update($collumns);
    }

    /**
     * @param array $collumns
     * @return DeleteBuilder
    */
    public function delete(array $collumns): DeleteBuilder {
        return (new DeleteBuilder($this->table, $this->repository))->delete($collumns);
    }

}