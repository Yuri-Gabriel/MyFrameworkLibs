<?php

namespace Framework\Libs\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Interceptor {
    public function __construct(
        public string $middleware
    ) {}
}