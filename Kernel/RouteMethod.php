<?php

namespace Framework\Kernel;


class RouteMethod {
    public function __construct(
        public object $controller,
        public string $name,
        public array $params,
        public array $middlewares
    ) { }
}