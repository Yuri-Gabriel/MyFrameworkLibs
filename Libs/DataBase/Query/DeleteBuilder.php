<?php

namespace Framework\Libs\DataBase\Query;

class DeleteBuilder extends Builder {

    /**
     * @param array $columns ["column" => "value"]
    */
    public function delete(array $columns): self {
        $this->query = "DELETE FROM {$this->table} WHERE ";
        $cols = [];
        foreach($columns as $key => $value) {
            $cols[] = "$key = $value";
        }
        $this->query .= implode(" AND ", $cols);
        return $this;
    }



}