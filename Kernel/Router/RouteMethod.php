<?php

namespace Framework\Kernel\Router;


class RouteMethod {
    public function __construct(
        public object $controller,
        public string $name,
        public array $params,
        public array $middlewares
    ) { }
}