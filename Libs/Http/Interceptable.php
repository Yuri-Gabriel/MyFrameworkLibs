<?php

namespace Framework\Libs\Http;

use Framework\Libs\Annotations\Rule;

interface Interceptable {
    public function rule(): bool;
}