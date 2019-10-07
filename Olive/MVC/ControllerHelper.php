<?php namespace Olive\MVC;

abstract class ControllerHelper
{

    /**
     * @param Controller $controller
     * @return mixed
     */
    public static function getName(Controller $controller) {
        return $controller->route->controller;
    }

    #region IO

    /**
     * @param $short_name
     * @return bool
     */
    final public static function exists($short_name) {
        $path = self::getPath($short_name);
        return file_exists($path) && isSubdirOf('App/Controllers', $path);
    }

    /**
     * @param $ctrl
     * @return string
     */
    final public static function getPath($ctrl) {
        return "App/Controllers/$ctrl.php";
    }

    #endregion

}
