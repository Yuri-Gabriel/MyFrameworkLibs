<?php

namespace Framework\Kernel;

use Exception;
use Framework\Libs\Annotations\Controller;
use Framework\Libs\Annotations\DataBase\Model;
use Framework\Libs\Annotations\Instantiate;
use Framework\Libs\Annotations\Interceptors;
use Framework\Libs\Annotations\Mapping;
use Framework\Libs\Http\Interceptable;
use ReflectionClass;
use ReflectionMethod;

require_once dirname(__DIR__) . "/vendor/autoload.php";

class RoutesKernel {
    private array $routes;

    public function __construct() {
        $pathControllers = $_SERVER["DOCUMENT_ROOT"] . "/App/Controller";
        $pathMiddlewares = $_SERVER["DOCUMENT_ROOT"] . "/App/Middleware";
        $pathModel = $_SERVER["DOCUMENT_ROOT"] . "/App/Model";

        $this->loadClasses($pathControllers);
        $this->loadClasses($pathMiddlewares);
        $this->loadClasses($pathModel);

        $allClasses = get_declared_classes();

        $controllerClasses = array_filter($allClasses, function($class) use ($pathControllers) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $pathControllers);
        });

        $modelClasses = array_filter($allClasses, function($class) use ($pathModel) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $pathModel);
        });

        $this->router($controllerClasses);    
        $this->chargeModels($modelClasses);   
    }

    public function listem(): void {
        // header("Content-Type: application/json");
        // echo json_encode(
        //     $this->routes
        // );
        // die();
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
                foreach ($class->getProperties() as $prop) {
                    $attr = $prop->getAttributes(Instantiate::class);
                    if(count($attr) > 0) {
                        $classNameToInstantiate = $prop->getType()->getName();

                        $dependencyInstance = new $classNameToInstantiate();
                        
                        $prop->setAccessible(true);
                        $prop->setValue($class_instance, $dependencyInstance);

                    }
                }

                foreach ($class->getMethods() as $method) {

                    $mapping_attrs = $method->getAttributes(Mapping::class);
                    if (empty($mapping_attrs)) {
                        continue; 
                    }

                    $mapping_attr = $mapping_attrs[0];
                    $mapping = $mapping_attr->newInstance();

                    $method_path = str_replace(' ', '', $mapping->path);
                    $method_path = $method_path == "/" ? "" : $method_path;

                    $path = $this->getRoutePath($root_path, $method_path);
                    
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

    private function chargeModels(array $modelClasses) {
        foreach ($modelClasses as $className) {
            $class = new ReflectionClass($className);
            $attributes = $class->getAttributes(Model::class);
            if(count($attributes) > 0) {
                $attr = $attributes[0];
                $attr_class = $attr->newInstance();
                print_r($attr_class);
                die;
            }
               
        }
    }

    private function getRoutePath($root_path, $method_path): string {
        if($root_path == '/' || $root_path == "") {
            if($method_path == '/' || $method_path == '') return '/';
            return $method_path;
        } else {
            return $root_path . $method_path;
        }
    }

    private function loadClasses(string $path) {
        if(!is_dir($path)) return;
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

    private function isModel(ReflectionClass $class): bool {
        $class_atributes = $class->getAttributes(Model::class);
        foreach($class_atributes as $attr) {
            if($attr->getName() == Model::class) {
                return true;
            }
        }
        return false;
    }

    private function getMiddleware(ReflectionMethod|ReflectionClass $obj): array {
        $middlewares = [];
        foreach($obj->getAttributes(Interceptors::class) as $interceptor_attr) {
            foreach($interceptor_attr->newInstance()->middlewares as $middle) {
                $middle_class = (new ReflectionClass(
                    $middle
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
                    $middlewares[] = [
                        'class' => $middle_class->newInstance(),
                        'method' => $rule
                    ];
                }
            }
            
        }
        return $middlewares;
    }
}
