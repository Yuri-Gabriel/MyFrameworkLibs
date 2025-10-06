<?php

namespace Framework\Libs\Annotations\DataBase;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Model {
    public function __construct(
        public string $table = ""
    ) {}
}