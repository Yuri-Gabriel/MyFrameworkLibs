<?php

namespace Framework\Libs\Http;

class Request {

    /**
     * @var array $body It's by default the json from the request body
    */
    public array $body;

    public function __construct(?array $body = null) {
        $this->body = isset($body) ? $body : $this->getJsonData();
    }

    /**
     * Get a value from input of forms
     * @param $key Name of input
     * @return string | null Will return a string value if exists a input with the key provided, else, return null
    */
    public function getInputValue(string $key): string | null {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }
    /**
     * Get a json from the request body
     * @return array Will return a array corresponding with the json
    */
    public function getJsonData(): array {
        return (array) json_decode(file_get_contents('php://input'));
    }
}