<?php

namespace Framework\Kernel;

use ReflectionClass;

class ClassLoader {
    public static function load(string $path) {
        $rootPath = dirname(__DIR__, 4);
        if(!str_contains($path,$rootPath)) {
            $path = $rootPath . $path;
        }
        if(str_contains($path,"view")) return;
        
        $content = scandir($path);

        foreach ($content as $item) {
            if ($item != '.' && $item != '..') {
                $full_path = $path . '/' . $item;
                if (is_dir($full_path)) {
                    self::load($path . "/" . $item);
                } else {
                    foreach (glob($path . "/*.php") as $file) {
                        require_once $file;
                    }
                }
            }
        }
    }

    public static function getClasses(string $pathClasses): array {
        $pathClasses = $_SERVER["DOCUMENT_ROOT"] . $pathClasses;
        $allClasses = get_declared_classes();
        return array_filter($allClasses, function($class) use ($pathClasses) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $pathClasses);
        });
    }
}

