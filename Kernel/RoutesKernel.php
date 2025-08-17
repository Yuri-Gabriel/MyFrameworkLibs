<?php

namespace Framework\Kernel;

use Framework\Libs\Http\Mapping;
use ReflectionClass;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class RoutesKernel {
    private array $routes;

    public function __construct() {
        $path = dirname(__DIR__, 4) . "/App/Controller";

        $this->loadControllers($path);

        $allClasses = get_declared_classes();

        $controllerClasses = array_filter($allClasses, function($class) use ($path) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $path);
        });

        $this->router($controllerClasses);

        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        $requestListener = new RequestListener($this->routes, $uri);
        $requestListener->dispatch();
        
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
                    $this->routes[$mapping->path] = new Route(
                        $instance,
                        $method->getName(),
                        $method->getParameters(),
                        $mapping->http_method
                    );
                }
            }
        }
    }

    private function loadControllers(string $path) {
        foreach (glob($path . "/*.php") as $file) {
            require_once $file;
        }
    }

    
}
