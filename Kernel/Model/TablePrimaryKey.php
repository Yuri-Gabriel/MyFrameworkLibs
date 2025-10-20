<?php

namespace Framework\Kernel\Model;

class TablePrimaryKey {
    public function __construct(
        public bool $autoincrement = true
    ) {}
}