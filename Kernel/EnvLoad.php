<?php

namespace Framework\Kernel;

class EnvLoad {

    private static $instance = null;
    private function __construct() {
        $envFilePath = dirname(__DIR__, 4) . '/.env';

        if (file_exists($envFilePath)) {
            $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');

                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * @return void
    */
    public static function load() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }
}

?>
