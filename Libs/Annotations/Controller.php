<?php

namespace Framework\Libs\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller {
    public function __construct(
        public string $path = ""
    ) {}
}