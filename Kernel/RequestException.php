<?php

namespace Framework\Kernel;

use Exception;

class RequestException extends Exception {
    public function __construct(string $message, int $http_status) {
        http_response_code($http_status);
        parent::__construct($message, $http_status);
    }
    public function __toString(): string {
        return __CLASS__ . ": " . $this->message;
    }
}