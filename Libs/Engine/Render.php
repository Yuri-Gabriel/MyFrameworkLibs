<?php

namespace Framework\Libs\Engine;

class Render {
    public static function render(string $view_name): void {
        $path =  $_SERVER['DOCUMENT_ROOT'];
        require $path . "/app/view/$view_name.php";
    }
}