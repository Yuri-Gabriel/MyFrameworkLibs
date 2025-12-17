<?php

namespace Framework\Libs\DataBase;

class InsertBuilder extends Builder {

    public function __construct(string $table) {
        parent::__construct($table);
    }

    public function insert(array $columns): self {
        return $this;
    }

}