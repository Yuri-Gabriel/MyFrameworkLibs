<?php

namespace Framework\Libs\DataBase;

class DeleteBuilder extends Builder {

    public function __construct(string $table) {
        parent::__construct($table);
    }

    public function delete(array $columns): self {
        return $this;
    }



}