<?php

namespace Framework\Kernel\Command;

use Framework\Libs\Exception\InputException;

class Run implements Inputable {
    private array $args;
    private array $params = [];

    public function __construct(array $args) {
        $this->args = $args;
    }

    public function run() {
        $port = null;
        $command = "php -S localhost:8000";
        
        foreach($this->args as $arg) {
            if(str_contains($arg, '--port')) {
                $port = explode(':', $arg)[1];
            }
        }

        if(isset($port)) $command = "php -S localhost:$port";
        
        foreach($this->params as $param) {
            $command .= $param;
        }

        exec($command);
    }

}