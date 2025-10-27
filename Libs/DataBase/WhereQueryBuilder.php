<?php

namespace Framework\Libs\DataBase;

class WhereQueryBuilder {
    private array $conditions;

    public function __construct() {
        $this->conditions = [];
    }

    public function and(string $column, string $operator, mixed $value): self {
        $this->conditions[] = ['AND', $column, $operator, $value];
        return $this;
    }

    public function or(string $column, string $operator, mixed $value): self {
        $this->conditions[] = ['OR', $column, $operator, $value];
        return $this;
    }

    public function isNull(string $column): self {
        $this->conditions[] = ['AND', $column, 'IS NULL', null];
        return $this;
    }

    public function notNull(string $column): self {
        $this->conditions[] = ['AND', $column, 'IS NOT NULL', null];
        return $this;
    }

    public function getSql(): string {
        $sql = '';
        foreach ($this->conditions as $i => [$logic, $col, $op, $val]) {
            $prefix = $i === 0 ? '' : " $logic ";
            $sql .= $prefix . "$col $op";
            if (!($op === 'IS NULL' || $op === 'IS NOT NULL'))  {
                $sql .= gettype($val) == 'string' ? " '$val'" : " $val";
            }
        }
        return $sql;
    }

}