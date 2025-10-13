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
            "default_function" => "\t#[Mapping('/')]\n\tpublic function main(): void {\n\n\t}"
        ],
        "middleware" => [
            "namespace" => "App\Middleware;",
            "annotation" => "#[Middleware]",
            "implements" => "Interceptable",
            "imports" => [
                "Framework\Libs\Annotations\Middleware",
                "Framework\Libs\Http\Interceptable"
            ],
            "default_function" => "\tpublic function rule(): bool {\n\t\treturn true;\n\t}"
        ],
        "view" => []
    ];
    public static function getContent(string $class_name, string $type): string {
        $contents = self::$contents[$type];

        $import_statements = '';
        if (isset($contents['imports']) && is_array($contents['imports'])) {
            foreach ($contents['imports'] as $import_class) {
                $import_statements .= "use {$import_class};" . PHP_EOL;
            }
        }

        $implementation = $contents['implements'] ? 'implements ' . $contents['implements'] : "";
        $annotation = $contents['annotation'] ?? "";

        $str = "<?php\n\nnamespace %s\n\n%s\n\n%s\nclass %s %s {\n%s\n}";
        
        return sprintf(
            $str,
            $contents['namespace'],
            $import_statements,
            $annotation,
            $class_name,
            $implementation,
            $contents['default_function']
        );
    }
}