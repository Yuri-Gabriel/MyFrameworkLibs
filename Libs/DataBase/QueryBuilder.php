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

    public function select(array $columns): SelectBuilder {
        return (new SelectBuilder($this->table, $this->repository))->select($columns);
    }

    public function insert(array $values): InsertBuilder  {
        return (new InsertBuilder($this->table, $this->repository))->insert($values);
    }

    public function update(array $values): UpdateBuilder  {
        return (new UpdateBuilder($this->table, $this->repository))->update($values);
    }

    public function delete(array $values): DeleteBuilder {
        return (new DeleteBuilder($this->table, $this->repository))->delete($values);
    }

}