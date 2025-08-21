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
        $this->currentRouteMethod = $this->getCurrentRouteMethod($this->routes, $this->uri);

        $this->getIntegredParameter();

        // if ($this->currentRouteMethod == null) {
        //     throw new RequestException("Route " . $_SERVER["REQUEST_URI"] . " not found", HTTP_STATUS::NOT_FOUND);
        // }

        // $request_method = $_SERVER["REQUEST_METHOD"];
        // if ($request_method != $this->currentRouteMethod->http_method) {
        //     throw new RequestException(
        //         "The method $request_method is not acceptable in route " . $this->uri,
        //         HTTP_STATUS::UNAUTHORIZED
        //     );
        // }

        // try {
        //     $listener = $this->listeners[$request_method];
        //     $this->$listener();
        // } catch (TypeError $err) {
        //     http_response_code(HTTP_STATUS::BAD_REQUEST);
        //     echo $err->getMessage();
        // }
    }

    private function getIntegredParameter() {
        $countOpenKey = 0;
        $countCloseKey = 0;
        foreach($this->routes as $k => $v) {
            if(str_contains($k, "{") || str_contains($k, "}")) {
                $pathArray = str_split($k);
                foreach($pathArray as $char) {
                    if($char == "{") {
                        $countOpenKey++;
                    } else if ($char == "}") {
                        $countCloseKey++;
                    }
                }
                if($countOpenKey != $countCloseKey) {
                    throw new RequestException(
                        "Invalid path: " . $k,
                        HTTP_STATUS::BAD_REQUEST
                    );
                }
                // Verificar de a $this->uri, rota atual, dar match com a rota $k
                $params = [];
                for($i = 0; $i < count($pathArray); $i++) {
                    if($pathArray[$i] == "{" && ($pathArray[$i + 1] != "{" || $pathArray[$i + 1] != "}")) {
                        $closeKeyPost = strpos(implode('', $pathArray), '}') + 1;
                        $i++;
                        $paramName = "";
                        for($j = $i; $j < $closeKeyPost; $j++) {
                            if($pathArray[$j] != "}") {
                                $paramName .= $pathArray[$j];
                            }
                            
                        }
                        $params[] = trim($paramName);
                    }
                    $pathArray[$i > 0 ? $i - 1 : $i] = ' ';
                }
                
            }
        }
    }

    private function listemGET(): void {
        $params = $this->getURIParams(
            $this->currentRouteMethod->params
        );

        $controllerName = $this->currentRouteMethod->controller;
        $controller = new $controllerName();

        $methodName = $this->currentRouteMethod->method;

        $ref = new ReflectionMethod($controller, $methodName);
        
        $ref->invokeArgs($controller, $params);
    }

    private function listemPOST(): void {
        // Criar a captura de parâmetros do tipo: https://site.com/{value}
        $params = $this->getBody(
            $this->currentRouteMethod->params
        );

        $controllerName = $this->currentRouteMethod->controller;
        $controller = new $controllerName();

        $methodName = $this->currentRouteMethod->method;

        $ref = new ReflectionMethod($controller, $methodName);
        
        $ref->invokeArgs($controller, $params);
    }

    private function listemPUT(): void {

    }

    private function listemDELETE(): void {

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
    private function getCurrentRouteMethod(array $routes, string $uri): Route | null {
        return isset($routes[$uri]) ? $routes[$uri] : null;
    }
}