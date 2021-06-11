<?php namespace Olive\Routing;


use manifest;
use Olive\Util\Str;

class Dispatcher
{
    public const NOT_FOUND          = 0;
    public const FOUND              = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /** @var array<string, array<string, mixed>> */
    protected array $staticRouteMap = [];

    /** @var array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>> */
    protected array $variableRouteData = [];

    /** @param array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>>} $data */
    public function __construct(array $data) {
        [$this->staticRouteMap, $this->variableRouteData] = $data;
    }

    /**
     * @param array $routeData
     * @param string $uri
     * @return array|null {0: int, 1?: list<string>|mixed, 2?: array<string, string>}|null
     */
    protected function dispatchVariableRoute(array $routeData, string $uri): ?array {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            [$handler, $varNames] = $data['routeMap'][count($matches)];

            $vars = [];
            $i    = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return [self::FOUND, $handler, $vars];
        }

        return null;
    }


    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * Returns array with one of the following formats:
     *
     *     [self::NOT_FOUND]
     *     [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [self::FOUND, $handler, ['varName' => 'value', ...]]
     *
     * @return array{0: int, 1?: list<string>|mixed, 2?: array<string, string>}
     */
    public function dispatch(): array {

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri        = trim($_SERVER['REQUEST_URI'], '/');
        $rootPath   = trim(manifest::root_path);
        if (Str::startsWith($rootPath, $uri))
            $uri = substr($uri, mb_strlen($rootPath));
        if ($uri === '')
            $uri = '/';

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            $handler = $this->staticRouteMap[$httpMethod][$uri];

            return [self::FOUND, $handler, []];
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result !== null) {
                return $result;
            }
        }

        // For HEAD requests, attempt fallback to GET
        if ($httpMethod === 'HEAD') {
            if (isset($this->staticRouteMap['GET'][$uri])) {
                $handler = $this->staticRouteMap['GET'][$uri];

                return [self::FOUND, $handler, []];
            }

            if (isset($varRouteData['GET'])) {
                $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        // If nothing else matches, try fallback routes
        if (isset($this->staticRouteMap['*'][$uri])) {
            $handler = $this->staticRouteMap['*'][$uri];

            return [self::FOUND, $handler, []];
        }

        if (isset($varRouteData['*'])) {
            $result = $this->dispatchVariableRoute($varRouteData['*'], $uri);
            if ($result !== null) {
                return $result;
            }
        }

        // Find allowed methods for this URI by matching against all other HTTP methods as well
        $allowedMethods = [];

        foreach ($this->staticRouteMap as $method => $uriMap) {
            if ($method === $httpMethod || !isset($uriMap[$uri])) {
                continue;
            }

            $allowedMethods[] = $method;
        }

        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result === null) {
                continue;
            }

            $allowedMethods[] = $method;
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods !== []) {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        }

        return [self::NOT_FOUND];
    }
}
