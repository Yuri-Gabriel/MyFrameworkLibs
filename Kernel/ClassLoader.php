<?php

namespace Framework\Kernel;

use ReflectionClass;

class ClassLoader {
    public static function load(string $path) {
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
        $allClasses = get_declared_classes();
        return array_filter($allClasses, function($class) use ($pathClasses) {
            $reflection = new ReflectionClass($class);
            return str_starts_with($reflection->getFileName(), $pathClasses);
        });
    }
}

