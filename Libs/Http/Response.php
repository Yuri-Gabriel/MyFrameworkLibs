<?php

namespace Framework\Libs\Http;

class Response {

    /**
     * @var array $body It's by default the json from the request body
    */
    public ?array $body;
    public int $http_code;

    public function __construct(int $http_code, ?array $body = null) {
        $this->body = $body;
        $this->http_code = $http_code;
    }

    public function setBody(array $body): void {
        $this->body = $body;
    }

    public function dispatch(): void  {
        http_response_code($this->http_code);
        header("Content-Type: application/json");
        echo json_encode(
            $this->body
        );
    }
}