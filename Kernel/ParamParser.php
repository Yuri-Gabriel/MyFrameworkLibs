<?php

namespace Framework\Kernel;

use Framework\Libs\Http\Request;

class ParamParser {

    public static function getURIParams(array $method_params): array {
        $params = [];
        foreach($method_params as $param) {
            $params[$param->name] = $_GET[$param->name] ?? null;
        }
        return $params;
    }

    public static function getBody(array $method_params): array {
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

    public static function getIntegredParameter(array $routes, string $current_uri): array | null {
        self::sortRoutes($routes);
        foreach($routes as $handler) {
            $regex = self::createRegexFromRoute($handler->path);
            if (preg_match($regex, $current_uri, $matches)) {
                array_shift($matches);
                
                $paramNames = self::getParamNames($handler->path);
                
                $params = array_combine($paramNames, $matches);
                
                return [$handler->path, $params];
            }
        }
        return null;
    }

    private static function sortRoutes(array $routes) {
        uasort($routes, function ($route1, $route2) {
            $literalParts1 = substr_count($route1->path, '/') - substr_count($route1->path, '{');
            $literalParts2 = substr_count($route2->path, '/') - substr_count($route2->path, '{');
            
            return $literalParts2 - $literalParts1;
        });
    }

    private static function createRegexFromRoute(string $route) {
        $tempRoute = preg_replace('/\{[A-Za-z0-9_]+\}/', '__PARAM__', $route);
        $escapedRoute = preg_quote($tempRoute, '~');
        $regex = str_replace('__PARAM__', '([^/]+)', $escapedRoute);

        return "~^" . $regex . "$~";
    }

    private static function getParamNames(string $route) {
        preg_match_all('/\{([A-Za-z0-9_]+)\}/', $route, $matches);
        return $matches[1];
    }

    
}