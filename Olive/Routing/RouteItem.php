<?php namespace Olive\Routing;

class RouteItem
{
    public string $httpMethod;

    public string $regex;

    /** @var array<string, string> */
    public array $variables;

    /** @var mixed */
    public $handler;

    /**
     * @param string $httpMethod
     * @param mixed $handler
     * @param string $regex
     * @param array<string, string> $variables
     */
    public function __construct(string $httpMethod, callable|array $handler, string $regex, array $variables) {
        $this->httpMethod = $httpMethod;
        $this->handler    = $handler;
        $this->regex      = $regex;
        $this->variables  = $variables;
    }

    /**
     * Tests whether this route matches the given string.
     */
    public function matches(string $str): bool {
        $regex = '~^' . $this->regex . '$~';

        return (bool)preg_match($regex, $str);
    }
}
