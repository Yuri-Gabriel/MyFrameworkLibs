<?php

namespace Framework\Libs\Annotations;

use Attribute;
use Framework\Libs\Http\Middleware;

#[Attribute(Attribute::TARGET_CLASS)]
class Interceptor {
    public function __construct(
        public Middleware $middleware
    ) {}
}