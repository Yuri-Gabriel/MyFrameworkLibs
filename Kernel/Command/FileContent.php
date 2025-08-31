<?php

namespace Framework\Kernel\Command;

class FileContent {
    private static array $contents = [
        "controller" => [
            "namespace" => "App\Controller;",
            "annotation" => "#[Controller]",
            "implements" => null,
            "imports" => [
                "Framework\Libs\Annotations\Controller",
                "Framework\Libs\Annotations\Mapping"
            ],
            "default_function" => "
                #[Mapping('/')]
                public function main(): void {
                    
                }
            "
        ],
        "middleware" => [
            "namespace" => "App\Middleware;",
            "annotation" => "#[Middleware]",
            "implements" => "Interceptable",
            "imports" => [
                "Framework\Libs\Annotations\Middleware",
                "Framework\Libs\Http\Interceptable"
            ],
            "default_function" => "
                public function rule(): bool {
                    return true;
                }
            "
        ],
        "view" => []
    ];
    public static function getContent(string $class_name, string $type): string {
        $contents = self::$contents[$type];
        $implementation = $contents['implements'] ?? "";
        $str = "
            <?php

            %s

            %s

            class %s %s {
                %s
            }
        ";
        return sprintf(
            $str,
            $contents['namespace'],
            $contents['imports'],
            $class_name,
            $implementation,
            $contents['default_function']
        );
    }
}