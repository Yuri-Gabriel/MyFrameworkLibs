<?php

namespace Framework\Libs\Annotations\DataBase;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Model {

    public function __construct(
        public string $table
    ) {}

    public function __set(string $key, string $value) {
        $this->$$key = $value;
    }

    public function __get(string $key) {
        return $this->$$key;
    }
}