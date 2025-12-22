<?php

namespace Framework\Libs\DataBase;

class InsertBuilder extends Builder {

    /**
     * @param array $columns ["column" => "value"]
    */
    public function insert(array $columns): self {
        $col = [];
        $val = [];
        foreach($columns as $key => $value) {
            $col[] = $key;
            $val[] = $value;
        }
        $this->query =
            "INSERT INTO {$this->table} (" . implode(", ", $col) . ") VALUES (" . implode(", ", $val) . ")"
        ; 
        return $this;
    }

}