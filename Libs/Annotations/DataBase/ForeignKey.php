<?php

namespace Framework\Libs\Annotations\DataBase;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey {
    public function __construct(
        public string $fk_column,
        public string $table
    ) {}
}