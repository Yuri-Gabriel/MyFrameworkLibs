<?php

namespace Framework\Kernel\Command;

use Framework\Libs\Exception\InputException;
use LDAP\Result;
use ReflectionClass;
use ReflectionMethod;

class Input {
    private array $argv;
    private int $argc;
    private array $commands = [
        'run' => Run::class,
        'create' => Create::class
    ];

    public function __construct(array $argv, int $argc) {
        $this->argc = $argc;
        $this->argv = $argv;
    }

    public function run() {
        $currentCommand = $this->commands[$this->argv[1]] ?? null;
        if(!isset($currentCommand)) throw new InputException(
            "Invalid command: {" . $this->argv[1] . "}"
        );

        $class = new ReflectionClass($currentCommand);
        $params = array_slice($this->argv, 2);
        $instance = new ($class->getName())($params);

        $run = new ReflectionMethod($instance, 'run');

        $run->invoke($instance);
    }

}