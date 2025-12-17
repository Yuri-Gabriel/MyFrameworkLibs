<?php

namespace Framework\Libs\DataBase;

abstract class Builder {
    protected string $query;
    protected string $table;

    public function __construct(string $table) {
        $this->query = "";
        $this->table = $table;
    }

    public function getQuery(): string {
        return $this->query;
    }
}