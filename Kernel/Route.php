<?php

namespace Framework\Kernel;

use Framework\Libs\Http\Middleware;

class Route {
    public function __construct(
        public string $path,
        public object $controller,
        public string $method,
        public array $params,
        public string $http_method,
        public array $middlewares
    ) { }
}