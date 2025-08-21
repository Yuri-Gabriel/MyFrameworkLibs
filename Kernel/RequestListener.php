<?php

namespace Framework\Kernel;

use Framework\Libs\Http\HTTP_STATUS;
use Framework\Libs\Http\Request;
use Framework\Libs\Http\RequestException;
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
        try {
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
            $rou = $this->getIntegredParameter();
            $this->$listener($rou[1]);
        } catch (RequestException $err) {
            echo json_encode([
                "message" => $err->getMessage()
            ]);
        }  catch (TypeError $err) {
            http_response_code(HTTP_STATUS::BAD_REQUEST);
            echo json_encode([
                "message" => $err->getMessage()
            ]);
        }
    }

    private function getIntegredParameter(): array | null {
        $this->sortRoutes();
        foreach($this->routes as $handler) {
            $regex = $this->createRegexFromRoute($handler->path);
            if (preg_match($regex, $this->uri, $matches)) {
                array_shift($matches);
                
                $paramNames = $this->getParamNames($handler->path);
                
                $params = array_combine($paramNames, $matches);
                
                return [$handler->path, $params];
            }
        }
        return null;
    }

    private function sortRoutes() {
        uasort($this->routes, function ($route1, $route2) {
            $literalParts1 = substr_count($route1->path, '/') - substr_count($route1->path, '{');
            $literalParts2 = substr_count($route2->path, '/') - substr_count($route2->path, '{');
            
            return $literalParts2 - $literalParts1;
        });
    }

    private function createRegexFromRoute(string $route) {
        $tempRoute = preg_replace('/\{[A-Za-z0-9_]+\}/', '__PARAM__', $route);
        $escapedRoute = preg_quote($tempRoute, '~');
        $regex = str_replace('__PARAM__', '([^/]+)', $escapedRoute);

        return "~^" . $regex . "$~";
    }

    private function getParamNames(string $route) {
        preg_match_all('/\{([A-Za-z0-9_]+)\}/', $route, $matches);
        return $matches[1];
    }

    private function listemGET(array $incorporedParams): void {

        $params = $this->getURIParams(
            $this->currentRouteMethod->params
        );

        $params = array_merge($params, $incorporedParams);

        $controllerName = $this->currentRouteMethod->controller;
        $controller = new $controllerName();

        $methodName = $this->currentRouteMethod->method;

        $ref = new ReflectionMethod($controller, $methodName);
        
        $ref->invokeArgs($controller, $params);
    }

    private function listemPOST(array $incorporedParams): void {
        $params = $this->getBody(
            $this->currentRouteMethod->params
        );

        $controllerName = $this->currentRouteMethod->controller;
        $controller = new $controllerName();

        $methodName = $this->currentRouteMethod->method;

        $ref = new ReflectionMethod($controller, $methodName);
        
        $ref->invokeArgs($controller, $params);
    }

    private function listemPUT(array $incorporedParams): void {

    }

    private function listemDELETE(array $incorporedParams): void {

    }

    private function getURIParams(array $method_params): array {
        $params = [];
        foreach($method_params as $param) {
            $params[$param->name] = $_GET[$param->name] ?? null;
        }
        return $params;
    }

    private function getBody(array $method_params): array {
        $params = [];
        foreach($method_params as $param) {
            if(str_contains($param->getType(), "Request")) {
                $params[$param->name] = new Request();
            } else {
                $params[$param->name] = $_GET[$param->name] ?? null;
            }
        }
        return $params;
    }
    private function getCurrentRouteMethod(array $routes): Route | null {
        $rou = $this->getIntegredParameter();

        $path = $this->uri;

        if(isset($rou) && count($rou[1]) > 0) $path = $rou[0];

        foreach($routes as $r) {
            if($r->path == $path) return $r;
        }
        
        return null;
    }
}