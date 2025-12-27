<?php

namespace Framework\Kernel\Router;

use Framework\Libs\Http\HTTP_STATUS;
use Framework\Libs\Exception\RequestException;
use Framework\Libs\Http\Response;
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
            $this->currentRouteMethod = $this->getCurrentRouteMethod($this->routes);

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

            $can_pass = false;
            $super_middlewares = $this->currentRouteMethod->middlewares;
            foreach($super_middlewares as $sm) {
                $method = new ReflectionMethod(
                    new $sm['class'](),
                    $sm['method']
                );
                $controller = $sm['class'];
                $can_pass = $method->invoke(new $controller(), []);
                if(!$can_pass) throw new RequestException(
                    "You can't access this route",
                    HTTP_STATUS::UNAUTHORIZED
                );
            }

            $children_middlewares = $this->currentRouteMethod->method->middlewares;
            foreach($children_middlewares as $cm) {
                $method = new ReflectionMethod(
                    new $cm['class'](),
                    $cm['method']
                );
                $controller = $cm['class'];
                $can_pass = $method->invoke(new $controller(), []);
                if(!$can_pass) throw new RequestException(
                    "You can't access this route",
                    HTTP_STATUS::UNAUTHORIZED
                );
            }

            $listener = $this->listeners[$request_method];
            $route = ParamParser::getIntegredParameter(
                $this->routes, 
                $this->uri
            );
            $this->$listener($route[1]);
        } catch (RequestException $err) {
            (new Response(
                $err->getCode(),
                [
                    "message" => $err->getMessage()
                ]
            ))->dispatch();
        }  catch (TypeError $err) {
            (new Response(
                HTTP_STATUS::BAD_REQUEST,
                [
                    "file" => $err->getFile(),
                    "line" => $err->getLine(),
                    "message" => $err->getMessage()
                ]
            ))->dispatch();
        }
    }

    private function listemGET(array $incorporedParams): void {
        $params = ParamParser::getURIParams(
            $this->currentRouteMethod->method->params
        );
        $params = array_merge($params, $incorporedParams);

        $controller = $this->currentRouteMethod->method->controller;

        $methodName = $this->currentRouteMethod->method->name;
        $ref = new ReflectionMethod($controller, $methodName);
        
        $ref->invokeArgs($controller, $params);

    }

    private function listemPOST(array $incorporedParams): void {
        $params = array_merge(ParamParser::getBody(
            $this->currentRouteMethod->method->params
        ), $incorporedParams);

        $controller = $this->currentRouteMethod->method->controller;

        $methodName = $this->currentRouteMethod->method->name;
        $ref = new ReflectionMethod($controller, $methodName);
        
        $ref->invokeArgs($controller, $params);
    }

    private function listemPUT(array $incorporedParams): void {
        $this->listemPOST($incorporedParams);
    }

    private function listemDELETE(array $incorporedParams): void {
        $this->listemPOST($incorporedParams);
    }

    
    private function getCurrentRouteMethod(array $routes): Route | null {
        $rou = ParamParser::getIntegredParameter(
            $this->routes, 
            $this->uri
        );

        $path = $this->uri;

        if(isset($rou) && count($rou[1]) > 0) $path = $rou[0];

        foreach($routes as $r) {
            if($r->path == $path) return $r;
        }
        
        return null;
    }
}