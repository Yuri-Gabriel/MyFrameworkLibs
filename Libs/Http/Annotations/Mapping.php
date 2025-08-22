<?php

namespace Framework\Libs\Http\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Mapping {
    public function __construct(
        public string $path,
        public string $http_method = "GET"
    ) {}
}