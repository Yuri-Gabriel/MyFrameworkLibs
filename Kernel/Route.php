<?php

namespace Framework\Kernel;

class Route {
    public function __construct(
        public object $controller,
        public string $method,
        public array $params,
        public string $http_method
    ) { }
}