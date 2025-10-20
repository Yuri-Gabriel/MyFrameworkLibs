<?php

namespace Framework\Kernel\Model;

class TableCollumn {
    public function __construct(
        public string $name,
        public string $type,
        public ?TablePrimaryKey $pk,
        public ?TableForeignKey $fk
    ) {}
}