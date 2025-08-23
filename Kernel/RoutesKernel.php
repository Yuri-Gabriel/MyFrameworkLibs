<?php

namespace Framework\Kernel;

use Framework\Libs\Http\Annotations\Controller;
use Framework\Libs\Http\Annotations\Mapping;
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
        //print_r($this->routes);
        $requestListener = new RequestListener($this->routes, $uri);
        $requestListener->dispatch();
        
    }

    private function router(array $controllerClasses) {
        foreach ($controllerClasses as $className) {
            $reflection = new ReflectionClass($className);
            $root_path = "";
            if($this->isController($reflection, $root_path)) {
                
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
                            $mapping->http_method
                        );
                    }
                }
            }
        }
    }

    private function loadControllers(string $path) {
        $conteudo = scandir($path); // Retorna um array com arquivos e pastas
        $pastas = [];

        foreach ($conteudo as $item) {
            // Ignora os diretórios . (diretório atual) e .. (diretório pai)
            if ($item != '.' && $item != '..') {
                $caminho_completo = $path . '/' . $item;
                if (is_dir($caminho_completo)) {
                    $this->loadControllers($path . "/" . $item); // Adiciona o nome da pasta ao array $pastas
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
}
