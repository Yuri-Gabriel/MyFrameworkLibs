<?php

namespace Framework\Libs\Annotations\DataBase;

use Attribute;
use Framework\Libs\Annotations\DataBase\Column;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[Column]
class ForeignKey {
    public function __construct(
        public string $fk_column,
        public string $table
    ) {}
}