<?php

namespace Framework\Kernel\Router;

use Framework\Libs\Http\Middleware;

class Route {
    public function __construct(
        public string $path,
        public string $http_method,
        public RouteMethod $method,
        public array $middlewares
    ) { }
}