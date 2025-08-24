<?php

namespace Framework\Kernel;

use Framework\Libs\Annotations\Controller;
use Framework\Libs\Annotations\Interceptor;
use Framework\Libs\Annotations\Mapping;
use Framework\Libs\Http\Middleware;
use Framework\Libs\Http\Response;
use ReflectionClass;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class RoutesKernel {
    private array $routes;

    public function __construct() {
        $pathControllers = dirname(__DIR__, 4) . "/App/Controller";
        $pathMiddlewares = dirname(__DIR__, 4) . "/App/Middleware";

        $this->loadClasses($pathControllers);
        $this->loadClasses($pathMiddlewares);

        $allClasses = get_declared_classes();

        $controllerClasses = array_filter($allClasses, function($class) use ($pathControllers) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $pathControllers);
        });

        $this->router($controllerClasses);        
    }

    public function listem(): void {
        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        $requestListener = new RequestListener($this->routes, $uri);
        $requestListener->dispatch();
    } 

    private function router(array $controllerClasses) {
        foreach ($controllerClasses as $className) {
            $reflection = new ReflectionClass($className);
            $root_path = "";
            $middlewares = [];
            if($this->isController($reflection, $root_path, $middlewares)) {
                
                if ($reflection->getName() === Mapping::class) continue;

                $instance = $reflection->newInstance();

                foreach ($reflection->getMethods() as $method) {
                    $attributes = $method->getAttributes(Mapping::class);
                    foreach ($attributes as $attribute) {
                        $mapping = $attribute->newInstance();

                        $method_path = str_replace(' ', '', $mapping->path);
                        $method_path = $method_path == "/" ? "" : $method_path;

                        $path = $root_path == '/' || $root_path == "" ? $method_path : $root_path . $method_path;
                        $this->routes[] = new Route(
                            $path,
                            $instance,
                            $method->getName(),
                            $method->getParameters(),
                            $mapping->http_method,
                            $middlewares
                        );
                    }
                }
            }
        }
    }

    private function loadClasses(string $path) {
        $content = scandir($path);

        foreach ($content as $item) {
            if ($item != '.' && $item != '..') {
                $full_path = $path . '/' . $item;
                if (is_dir($full_path)) {
                    $this->loadClasses($path . "/" . $item);
                } else {
                    foreach (glob($path . "/*.php") as $file) {
                        require_once $file;
                    }
                }
            }
        }
    }

    private function isController(ReflectionClass $class, string &$root_path, array &$middlewares): bool {
        $class_atributes = $class->getAttributes(Controller::class);
        foreach($class_atributes as $attr) {
            if($attr->getName() == Controller::class) {
                $interceptors = $class->getAttributes(Interceptor::class);
                foreach($interceptors as $i) {
                    $middlewares[] = (new ReflectionClass(
                        $i->newInstance()->middleware
                    ))->newInstance();
                }
                $root_path = str_replace(' ', '', $attr->newInstance()->path);
                return true;
            }
        }
        $middlewares = [];
        $root_path = "";
        return false;
    }
}
