<?php

namespace Framework\Libs\Http;

abstract class Middleware {
    abstract public function rule(): bool;
}