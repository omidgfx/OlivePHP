<?php namespace Olive\Routing;

use Olive\Core;

abstract class Controller extends Core {
    /** @var Route */
    private $route;

    public abstract function fnIndex($args = []);


    public function __construct(Route $route) {
        $this->route = $route;
    }

    /**
     * @param $ctrl
     * @return string
     */
    public static function getPath($ctrl) {
        return "App/Controllers/$ctrl.php";
    }

    /**
     * @param $short_name
     * @return bool
     */
    public static function exists($short_name) {
        return file_exists(self::getPath($short_name));
    }


    /**
     * @return Route
     */
    public function getRoute() {
        return $this->route;
    }

}