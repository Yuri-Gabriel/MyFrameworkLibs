<?php

namespace Framework\Kernel;

use Exception;
use Framework\Libs\Annotations\Controller;
use Framework\Libs\Annotations\Interceptor;
use Framework\Libs\Annotations\Mapping;
use Framework\Libs\Annotations\Rule;
use Framework\Libs\Http\Interceptable;
use Framework\Libs\Http\Middleware;
use Framework\Libs\Http\Response;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

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
            $class = new ReflectionClass($className);
            $root_path = "";
            if($this->isController($class, $root_path)) {
                
                if ($class->getName() === Mapping::class) continue;

                $class_instance = $class->newInstance();

                foreach ($class->getMethods() as $method) {

                    $mapping_attr = $method->getAttributes(Mapping::class)[0];
                    $mapping = $mapping_attr->newInstance();

                    $method_path = str_replace(' ', '', $mapping->path);
                    $method_path = $method_path == "/" ? "" : $method_path;

                    $path = $root_path == '/' || $root_path == "" ? $method_path : $root_path . $method_path;

                    $this->routes[] = new Route(
                        $path,
                        $mapping->http_method,
                        new RouteMethod(
                            $class_instance,
                            $method->getName(),
                            $method->getParameters(),
                            $this->getMiddleware($method)
                        ),
                        $this->getMiddleware($class)
                    );
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

    private function isController(ReflectionClass $class, string &$root_path): bool {
        $class_atributes = $class->getAttributes(Controller::class);
        foreach($class_atributes as $attr) {
            if($attr->getName() == Controller::class) {
                $root_path = str_replace(' ', '', $attr->newInstance()->path);
                return true;
            }
        }
        $root_path = "";
        return false;
    }

    private function getMiddleware(ReflectionMethod|ReflectionClass $obj): array {
        foreach($obj->getAttributes(Interceptor::class) as $interceptor_attr) {
            $middle_class = (new ReflectionClass(
                $interceptor_attr->newInstance()->middleware
            ));
            if(!($middle_class->newInstance() instanceof Interceptable)) throw new Exception(
                $middle_class::class . " need to implement " . Interceptable::class
            );
            $rule = null;
            foreach($middle_class->getMethods() as $method) {
                if($method->getName() != "rule") continue;
                $rule = $method->getName();
                break;
            }
            if(isset($rule)) {
                return [
                    'class' => $middle_class->newInstance(),
                    'method' => $rule
                ];
            }
        }
        return [];
    }
}
