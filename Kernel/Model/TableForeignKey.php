<?php

namespace Framework\Kernel\Model;

class TableForeignKey {
    public function __construct(
        public string $other_table,
        public string $from_collumn,
        public string $to_collumn
    ) {}
}