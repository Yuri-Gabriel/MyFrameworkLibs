<?php

namespace Framework\Kernel;

use ReflectionClass;

use Framework\Kernel\Model\ModelKernel;
use Framework\Kernel\Router\RoutesKernel;

// require_once dirname(__DIR__) . "/vendor/autoload.php";

class Kernel {

    /** @var array<Kernable> */
    private array $kernels;
    public function __construct() {
        $this->kernels = [
            ModelKernel::class,
            RoutesKernel::class
        ];
    }

    public function start(): void {
        foreach($this->kernels as $kernel) {
            $class = new ReflectionClass($kernel);
            $instance = $class->newInstance();
            $instance->run();
        }
    }
}