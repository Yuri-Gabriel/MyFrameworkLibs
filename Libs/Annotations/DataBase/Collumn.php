<?php

namespace Framework\Libs\Annotations\DataBase;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Collumn {
    public function __construct(
        public mixed $defaultValue = ""
    ) {}
}