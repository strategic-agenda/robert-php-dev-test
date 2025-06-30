<?php

namespace Kirilmaz\Interview\Core;

class RouteParser {
    protected string $inputRoute = '';
    protected string $outputRoute = '';
    protected array $outputRouteParts = [];
    protected array $variables = [];

    function __construct($route) {
        $this->inputRoute = $route;
        $this->parse();
    }

    public function route(): string {
        return $this->outputRoute;
    }

    public function variables(): array {
        return $this->variables;
    }

    private function parse(): void {
        $inputRouteParts = array_filter(explode('/', $this->inputRoute));

        if(!empty(count($inputRouteParts))) {
            foreach(explode('/', $this->inputRoute) AS $part) {
                if(!empty(strpos($part, '}'))) {
                    $this->variables[] = $this->variable($part);
                }

                $this->outputRouteParts[] .= $part;
            }
        }

        $this->outputRoute = implode('/', $this->outputRouteParts);
    }

    private function variable($input): string {
        return preg_replace('/{(.*?)}/', '${1}', $input);
    }
}
