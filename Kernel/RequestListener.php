<?php

namespace Framework\Kernel;

use Framework\Libs\Http\HTTP_STATUS;
use ReflectionMethod;
use TypeError;

class RequestListener {

    private array $routes;
    private string $uri;
    private ?Route $currentRouteMethod;
    private array $listeners = [
        "GET" => "listemGET",
        "POST" => "listemPOST",
        "PUT" => "listemPUT",
        "DELETE" => "listemDELETE",
    ];
    public function __construct(array $routes, string $uri) {
        $this->routes = $routes;
        $this->uri = $uri;
    }

    public function dispatch() {
        $this->currentRouteMethod = $this->getCurrentRouteMethod($this->routes, $this->uri);

        if ($this->currentRouteMethod == null) {
            throw new RequestException("Route " . $_SERVER["REQUEST_URI"] . " not found", HTTP_STATUS::NOT_FOUND);
        }

        $request_method = $_SERVER["REQUEST_METHOD"];
        if ($request_method != $this->currentRouteMethod->http_method) {
            throw new RequestException(
                "The method $request_method is not acceptable in route " . $this->uri,
                HTTP_STATUS::UNAUTHORIZED
            );
        }

        
        $listener = $this->listeners[$request_method];
        $this->$listener();
    }

    private function listemGET(): void {
        try {
            $params = $this->getURIParams(
                $this->currentRouteMethod->params
            );

            $controllerName = $this->currentRouteMethod->controller;
            $controller = new $controllerName();

            $methodName = $this->currentRouteMethod->method;

            $ref = new ReflectionMethod($controller, $methodName);
            
            $ref->invokeArgs($controller, $params);
        } catch (TypeError $err) {
            http_response_code(HTTP_STATUS::BAD_REQUEST);
            echo $err->getMessage();
        }
    }

    private function listemPOST(): void {

    }

    private function listemPUT(): void {

    }

    private function listemDELETE(): void {

    }

    private function getURIParams(array $method_params) {
        $params = [];
        foreach($method_params as $param) {
            $params[$param->name] = $_GET[$param->name] ?? null;
        }
        return $params;
    }
    private function getCurrentRouteMethod(array $routes, string $uri): Route | null {
        return isset($routes[$uri]) ? $routes[$uri] : null;
    }
}