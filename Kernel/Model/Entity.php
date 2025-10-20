<?php

namespace Framework\Kernel\Model;

class Entity {
    public function __construct(
        public string $table,
        public array $collunmns
    ) {}
}