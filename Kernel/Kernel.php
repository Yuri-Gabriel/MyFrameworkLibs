<?php

namespace Framework\Kernel;

use ReflectionClass;

use Framework\Kernel\Model\ModelKernel;
use Framework\Kernel\Router\RoutesKernel;

class Kernel {

    /** @var array<Kernable> */
    private array $kernels;

    /** @var Kernel $instance */
    private static $instance;

    private function __construct() {

        ClassLoader::load("/app");

        $this->kernels = [
            ModelKernel::class,
            RoutesKernel::class
        ];
    }

    public static function getInstance() : self {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function startKernel(string $kernelClass): void {
        $class = new ReflectionClass($kernelClass);
        $instance = $class->newInstance();
        $instance->run();
    }
}