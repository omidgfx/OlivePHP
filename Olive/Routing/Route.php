<?php namespace Olive\Routing;

use Olive\Traits\ProtectedSingleton;

/**
 * Class Route
 * @param array|string $route =[
 *     'prefix'=>'',
 *     'middlewares'=>[]
 * ]
 * @method static get(array|string $route, callable|array $handler)
 * @method static post(array|string $route, callable|array $handler)
 * @method static put(array|string $route, callable|array $handler)
 * @method static delete(array|string $route, callable|array $handler)
 * @method static patch(array|string $route, callable|array $handler)
 * @method static head(array|string $route, callable|array $handler)
 * @method static group(array|string $options, callable|array $routes)
 *
 * @package Olive\Routing
 *
 */
class Route
{
    use ProtectedSingleton;

    private Collector $collector;

    protected function _singletonConstruct(): void {
        $parser          = new Parser;
        $generator       = new Generator;
        $this->collector = new Collector($parser, $generator);
    }


    public static function __callStatic($name, $arguments) {
        $instance = self::getInstance();
        if (method_exists($instance->collector, $name))
            $instance->collector->{$name}($arguments[0], $arguments[1]);
    }

    public static function getCollector(): Collector {
        return self::getInstance()->collector;
    }

}