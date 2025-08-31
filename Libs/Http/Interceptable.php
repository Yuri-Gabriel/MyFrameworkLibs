<?php

namespace Framework\Libs\Http;

interface Interceptable {
    public function rule(): bool;
}