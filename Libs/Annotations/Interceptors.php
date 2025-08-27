<?php

namespace Framework\Libs\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Interceptors {
    public function __construct(
        public array $middlewares
    ) {}
}