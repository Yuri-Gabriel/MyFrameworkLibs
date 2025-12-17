<?php

namespace Framework\Libs\DataBase;

class SelectBuilder extends Builder {

    public function __construct(string $table) {
        parent::__construct($table);
    }

    public function select(array $columns): self {
        $this->query .= " SELECT ";
        for($i = 0; $i < count($columns); $i++) {
            $this->query .= $i == count($columns) - 1 ? $columns[$i] . ' ' : $columns[$i] . ', ';
        }
        $this->query .= " \nFROM " . $this->table . " ";
        return $this;
    }

    public function where(callable $func): self {
        $where = new WhereQueryBuilder();

        $func($where);

        $whereSql = $where->getSql();
        if($whereSql != '') {
            $this->query .= " \nWHERE $whereSql ";
        }
        return $this;
    }

    public function innerJoin(string $table, string $from, string $operation, string $to): self {
        $this->query .= " \nINNER JOIN $table ON $from $operation $to";
        return $this;
    }

    public function leftJoin(string $table, string $from, string $operation, string $to): self {
        $this->query .= " \nLEFT JOIN $table ON $from $operation $to";
        return $this;
    }

    public function rightJoin(string $table, string $from, string $operation, string $to): self {
        $this->query .= " \nRIGHT JOIN $table ON $from $operation $to";
        return $this;
    }

    public function orderByASC(string $collumn): self {
        if(!str_contains($this->query, "ORDER BY")) {
            $this->query .= " \nORDER BY ";
        }
        $this->query .= " $collumn ASC ";
        return $this;
    }

    public function orderByDESC(string $collumn): self {
        if(!str_contains($this->query, "ORDER BY")) {
            $this->query .= " \nORDER BY ";
        }
        $this->query .= " $collumn DESC ";
        return $this;
    }


    public function limit(int $limit): self {
        $this->query .= "\nLIMIT $limit";
        return $this;
    }


}