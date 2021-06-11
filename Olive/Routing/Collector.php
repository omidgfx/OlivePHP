<?php namespace Olive\Routing;

use Olive\Exceptions\InvalidRoute;

class Collector
{
    protected Parser $parser;

    protected Generator $generator;

    protected string $currentGroupPrefix = '';

    /** @var array */
    protected array $currentGroupMiddlewares = [];

    public function __construct(Parser $parser, Generator $generator) {
        $this->parser    = $parser;
        $this->generator = $generator;
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param array|string $options [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable|array $callback
     */
    public function group(array|string $options, callable|array $callback): void {

        $previousGroupPrefix      = $this->currentGroupPrefix;
        $previousGroupMiddlewares = $this->currentGroupMiddlewares;

        if (is_array($options)) {
            $this->currentGroupPrefix      = $previousGroupPrefix . $options['prefix'];
            $this->currentGroupMiddlewares = array_unique(array_merge($previousGroupMiddlewares, $options['middlewares'] ?? []));
        } else {
            $this->currentGroupPrefix = $previousGroupPrefix . $options;
        }

        $callback();

        $this->currentGroupMiddlewares = $previousGroupMiddlewares;
        $this->currentGroupPrefix      = $previousGroupPrefix;
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param array|string $options [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable|array $handler
     * @throws InvalidRoute
     */
    public function addRoute(array|string $httpMethod, array|string $options, callable|array $handler): void {
        $middlewares = [];
        if (is_array($options)) {
            $middlewares = $options['middlewares'] ?? [];
            $options     = $this->currentGroupPrefix . $options['prefix'];
        } else {
            $options = $this->currentGroupPrefix . $options;
        }
        $datum       = $this->parser->parse($options);
        $middlewares = array_unique(array_merge($middlewares, $this->currentGroupMiddlewares ?? []));
        foreach ((array)$httpMethod as $method) {
            foreach ($datum as $routeData) {
                $this->generator->addRoute($method, $routeData, new Handler($handler, $middlewares));
            }
        }
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable|array $handler
     * @throws InvalidRoute
     */
    public function get(array|string $route, callable|array $handler): void {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable|array $handler
     * @throws InvalidRoute
     */
    public function post(array|string $route, callable|array $handler): void {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable|array $handler
     * @throws InvalidRoute
     */
    public function put(array|string $route, callable|array $handler): void {
        $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable|array $handler
     * @throws InvalidRoute
     */
    public function delete(string|array $route, callable|array $handler): void {
        $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable $handler
     * @throws InvalidRoute
     */
    public function patch(string|array $route, callable $handler): void {
        $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable $handler
     * @throws InvalidRoute
     */
    public function head(string|array $route, callable $handler): void {
        $this->addRoute('HEAD', $route, $handler);
    }

    /**
     * Adds an OPTIONS route to the collection
     *
     * This is simply an alias of $this->addRoute('OPTIONS', $route, $handler)
     *
     * @param string|array $route [
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable $handler
     * @throws InvalidRoute
     */
    public function options(string|array $route, callable $handler): void {
        $this->addRoute('OPTIONS', $route, $handler);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>>}
     */
    public function getData(): array {
        return $this->generator->getData();
    }

    /**
     * Adds a route to the collection with any method
     *
     * This is simply an alias of $this->addRoute('*', $route, $handler)
     *
     * @param array|string $route =[
     *     'prefix'=>'',
     *     'middlewares'=>[]
     * ]
     * @param callable $handler
     * @throws InvalidRoute
     */
    public function any(array|string $route, callable $handler): void {
        $this->addRoute('*', $route, $handler);
    }

}
