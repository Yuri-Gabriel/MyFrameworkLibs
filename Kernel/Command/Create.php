<?php

namespace Framework\Kernel\Command;

use Framework\Libs\Exception\InputException;

class Create implements Inputable {
    private array $args;
    private array $create_commands = [
        'controller' => '/App/Controller',
        'middleware' => '/App/Middleware',
        'view' => '/App/View'
    ];

    public function __construct(array $args) {
        $this->args = $args;
    }

    public function run() {
        $path = $this->create_commands[$this->args[0]] ?? null;

        if(!isset($path)) throw new InputException(
            "Invalid command: {" . $this->args[0] . "}"
        );

        $file_name = $this->args[1];

        if(!isset($file_name)) throw new InputException(
            "The file name don't be null"
        );

        $this->makeFile(
            $file_name,
            $path,
            FileContent::getContent(
                explode(".", $file_name)[0]
                
            )
        );
    }

    private function makeFile(string $file_name, string $path, string $file_content) {
        $dir = dirname(__DIR__, 5) . $path;
        
        if(is_dir($dir)) {
            file_put_contents(
                $dir . "/" . $file_name,
                ""
            );
        } else {
            mkdir($dir, 0777, true);
            $this->makeFile($file_name, $path);
        }
    }
}