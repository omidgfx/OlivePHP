<?php namespace Olive\MVC;

use Olive\Core;
use Olive\Routing\Route;

abstract class Middleware extends Core
{
    public static function exists($short_name) {
        return file_exists(self::getPath($short_name));
    }

    public static function getPath($ctrl) {
        return "App/Middlewares/$ctrl.php";
    }

    /**
     * @param Route $route
     * @param array $args
     * @return bool
     */
    abstract public function execute(Route $route, $args = []);

}
