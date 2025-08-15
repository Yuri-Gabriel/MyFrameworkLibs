<?php

namespace Framework\Kernel;

use Framework\Libs\Http\Mapping;
use Framework\Libs\Http\HTTP_STATUS;
use ReflectionClass;
use ReflectionMethod;
use TypeError;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class RoutesKernel {
    private array $routes;
    private string $uri;

    public function __construct() {
        $path = dirname(__DIR__, 4) . "/App/Controller";

        $this->loadControllers($path);

        $allClasses = get_declared_classes();

        $controllerClasses = array_filter($allClasses, function($class) use ($path) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $path);
        });

        $this->router($controllerClasses);

        $this->uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if ($this->getCurrentRouteMethod() != null) {
            $request_method = $_SERVER["REQUEST_METHOD"];
            if($request_method == $this->getCurrentRouteMethod()["http_method"]) {
                switch($request_method) {
                    case "GET":
                        $this->listemGET();
                        break;
                    case "POST":
                        $this->listemPOST();
                        break;
                    case "PUT":
                        $this->listemPUT();
                        break;
                    case "DELETE":
                        $this->listemDELETE();
                        break;
                }
            } else {
                http_response_code(HTTP_STATUS::UNAUTHORIZED);
                header('Content-Type: application/json');
                echo json_encode([
                    "Error" => "The method $request_method is not acceptable in route $this->uri"
                ]);
            }
        } else {
            http_response_code(response_code: HTTP_STATUS::NOT_FOUND);
            header('Content-Type: application/json');
            echo json_encode([
                "Error" => "Route " . $_SERVER["REQUEST_URI"] . " not found"
            ]);
        }
    }

    private function listemGET(): void {
        try {
            $params = $this->getURIParams(
                $this->getCurrentRouteMethod()["params"]
            );

            $controllerName = $this->getCurrentRouteMethod()['controller'];
            $controller = new $controllerName();

            $methodName = $this->getCurrentRouteMethod()['method'];

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

    private function getCurrentRouteMethod(): array {
        return $this->routes[$this->uri];
    }

    private function router(array $controllerClasses) {
        foreach ($controllerClasses as $className) {
            $reflection = new ReflectionClass($className);

            if ($reflection->getName() === Mapping::class) continue;

            $instance = $reflection->newInstance();

            foreach ($reflection->getMethods() as $method) {
                $attributes = $method->getAttributes(Mapping::class);
                foreach ($attributes as $attribute) {
                    $mapping = $attribute->newInstance();
                    $this->routes[$mapping->path] = [
                        'controller' => $instance,
                        'method' => $method->getName(),
                        'params' => $method->getParameters(),
                        'http_method' => $mapping->http_method
                    ];
                }
            }
        }
    }

    private function loadControllers(string $path) {
        foreach (glob($path . "/*.php") as $file) {
            require_once $file;
        }
    }

    private function getURIParams(array $method_params) {
        $params = [];
        foreach($method_params as $param) {
            $params[$param->name] = $_GET[$param->name] ?? null;
        }
        return $params;
    }
}
