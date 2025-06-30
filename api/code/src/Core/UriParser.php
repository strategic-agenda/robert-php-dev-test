<?php

namespace Kirilmaz\Interview\Core;

class UriParser {
    protected string $url;
    protected string $httpMethod;
    protected array $parts;

    function __construct(string $url, string $httpMethod) {
        $this->url = $url;
        $this->httpMethod = $httpMethod;

        $this->parts = array_filter(explode('/', $url));
    }

    public function parts(): array {
        return $this->parts;
    }

    public function getPart($number): string {
        if($number >= count($this->parts)) {
            pd('bad request', true);
        }

        return $this->parts[$number];
    }

    public function url(): string {
        return $this->url;
    }

    public function getHttpMethod(): string {
        return strtolower($this->httpMethod);
    }
}
