<?php

namespace Framework\Libs\DataBase;

class QueryBuilder {
    private string $table;

    public function __construct(string $table) {
        $this->table = $table;
    }

    public function select(array $columns): SelectBuilder {
        return (new SelectBuilder($this->table))->select($columns);
    }

    public function insert(array $values): InsertBuilder  {
        return (new InsertBuilder($this->table))->insert($values);
    }

    public function update(array $values): UpdateBuilder  {
        return (new UpdateBuilder($this->table))->update($values);
    }

    public function delete(array $values): DeleteBuilder {
        return (new DeleteBuilder($this->table))->delete($values);
    }

}