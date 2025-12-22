<?php

namespace Framework\Libs\DataBase;

class UpdateBuilder extends Builder {

    /**
     * @param array $columns ["column" => "value"]
    */
    public function update(array $columns): self {
        $this->query = "UPDATE {$this->table} SET ";
        foreach($columns as $key => $value) {
            $this->query .= "$key = $value";
        }
        return $this;
    }

}