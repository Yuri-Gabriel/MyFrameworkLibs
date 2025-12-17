<?php

namespace Framework\Libs\DataBase;

class UpdateBuilder {
    private string $query;
    private string $table;

    public function __construct(string $table) {
        $this->query = "";
        $this->table = $table;
    }

    public function get(): string {
        return $this->query;
    }

    public function update(array $columns): self {
        return $this;
    }

}