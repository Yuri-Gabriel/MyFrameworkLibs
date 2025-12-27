<?php

namespace Framework\Libs\Exception;

use Exception;
class ModelException extends Exception {
    public function __construct(string $message) {
        parent::__construct($message);
    }
    public function __toString(): string {
        return __CLASS__ . ": " . $this->message;
    }
}