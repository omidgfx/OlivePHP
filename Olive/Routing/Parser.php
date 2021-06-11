<?php namespace Olive\Routing;

use Olive\Exceptions\InvalidRoute;

/**
 * Parses route strings of the following form:
 *
 * "/user/{name}[/{id:[0-9]+}]"
 */
class Parser
{
    public const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;

    public const DEFAULT_DISPATCH_REGEX = '[^/]+';

    /**
     * Parses a route string into multiple route data arrays.
     *
     * The expected output is defined using an example:
     *
     * For the route string "/fixedRoutePart/{varName}[/moreFixed/{varName2:\d+}]", if {varName} is interpreted as
     * a placeholder and [...] is interpreted as an optional route part, the expected result is:
     *
     * [
     *     // first route: without optional part
     *     [
     *         "/fixedRoutePart/",
     *         ["varName", "[^/]+"],
     *     ],
     *     // second route: with optional part
     *     [
     *         "/fixedRoutePart/",
     *         ["varName", "[^/]+"],
     *         "/moreFixed/",
     *         ["varName2", [0-9]+"],
     *     ],
     * ]
     *
     * Here one route string was converted into two route data arrays.
     *
     * @param string $route Route string to parse
     *
     * @return array[] Array of route data arrays
     * @throws InvalidRoute
     */
    public function parse(string $route): array {
        $routeWithoutClosingOptionals = rtrim($route, ']');
        $numOptionals                 = strlen($route) - strlen($routeWithoutClosingOptionals);

        // Split on [ while skipping placeholders
        $segments = preg_split('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \[~x', $routeWithoutClosingOptionals);

        if ($numOptionals !== count($segments) - 1) {
            // If there are any ] in the middle of the route, throw a more specific error message
            if (preg_match('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \]~x', $routeWithoutClosingOptionals)) {
                throw new InvalidRoute('Optional segments can only occur at the end of a route');
            }

            throw new InvalidRoute("Number of opening '[' and closing ']' does not match");
        }

        $currentRoute = '';
        $routeData    = [];

        foreach ($segments as $n => $segment) {
            if ($segment === '' && $n !== 0) {
                throw new InvalidRoute('Empty optional part');
            }

            $currentRoute .= $segment;
            $routeData[]  = $this->parsePlaceholders($currentRoute);
        }

        return $routeData;
    }

    /**
     * Parses a route string that does not contain optional segments.
     *
     * @param string $route
     * @return array
     */
    private function parsePlaceholders(string $route): array {
        if (!preg_match_all('~' . self::VARIABLE_REGEX . '~x', $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return [$route];
        }

        $offset    = 0;
        $routeData = [];

        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $routeData[] = substr($route, $offset, $set[0][1] - $offset);
            }

            $routeData[] = [
                $set[1][0],
                isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX,
            ];

            $offset = $set[0][1] + strlen($set[0][0]);
        }

        if ($offset !== strlen($route)) {
            $routeData[] = substr($route, $offset);
        }

        return $routeData;
    }
}
