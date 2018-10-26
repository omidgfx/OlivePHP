<?php namespace Olive\Routing;

use Olive\Core;

abstract class Middleware extends Core {
    /**
     * @param Route $route
     * @param array $args
     * @return bool
     */
    public abstract function perform(Route $route, $args = []);

    public static function getPath($ctrl) {
        return "App/Middlewares/$ctrl.php";
    }

    public static function exists($short_name) {
        return file_exists(self::getPath($short_name));
    }

}