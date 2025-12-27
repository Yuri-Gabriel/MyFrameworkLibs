<?php

namespace Framework\Kernel\Command;

use Framework\Kernel\Kernel;
use Framework\Kernel\Model\ModelKernel;

class Run implements Inputable {
    private array $args;
    private array $params = [];

    public function __construct(array $args) {
        $this->args = $args;
    }

    public function run() {
        $port = null;
        $command = "php -S 0.0.0.0:8000";
        
        foreach($this->args as $arg) {
            if(str_contains($arg, '--port')) {
                $port = explode(':', $arg)[1];
            }
        }

        if(isset($port)) $command = "php -S 0.0.0.0:$port";
        
        foreach($this->params as $param) {
            $command .= $param;
        }

        Kernel::getInstance()->startKernel(ModelKernel::class);

        exec($command);
    }

}