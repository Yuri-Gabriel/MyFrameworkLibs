<?php

namespace Framework\Libs\Annotations\DataBase;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey {
    public function __construct(
        public bool $autoincrement = true
    ) { }
}