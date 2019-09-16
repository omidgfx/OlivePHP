<?php namespace Olive\Routing;

use Olive\Core;
use Olive\Exceptions\H404;

abstract class Controller extends Core
{

    //region Fields

    /** @var Route */
    private $route;

    //endregion

    //region Abstracts

    public abstract function fnIndex($args = []);

    //endregion

    //region Constructors

    public function __construct(Route $route) {
        $this->route = $route;
    }

    //endregion

    //region IO

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
        $path = self::getPath($short_name);
        return isSubdirOf("App/Controllers", $path) && file_exists($path);
    }

    //endregion

    //region Routing

    /**
     * @return Route
     */
    public function getRoute() {
        return $this->route;
    }

    //endregion

    //region Front-End
    /**
     * @param string $view_name
     * @param array $params associated array, keys are variables name and accessible in rendering view and layout
     * @param string $layout name of layout, by passing null, uses $layout in view
     * @throws H404
     */
    protected function renderView($view_name, $params = [], $layout = null) {
        ob_start();

        $vars = $this->require("App/Views/$view_name.php", $params);

        if (!isset($vars['layout']))
            $vars['layout'] = $layout;
        elseif (!$layout)
            $layout = $vars['layout'];

        $viewContent = ob_get_clean();

        $this->renderLayout($layout, array_merge($params, ['content' => $viewContent]));
    }

    /**
     * @param string $path script php related path
     * @param array $variables
     * @return array defined variables in script
     * @throws H404
     */
    public function require($path, $variables = []) {
        if (!file_exists($path))
            throw new H404(DEBUG_MODE ? $path : 'Resource not found.');
        extract($variables);
        /** @noinspection PhpIncludeInspection */
        require $path;
        return get_defined_vars();
    }

    /**
     * @param $layout
     * @param array $vars
     * @throws H404
     */
    private function renderLayout($layout, $vars = []) {
        if ($layout == null) {
            echo $vars['content'];
            return;
        }
        ob_start();
        $res            = self::require("App/Layouts/$layout.php", $vars);
        $layout_content = ob_get_clean();
        if (isset($res['parent_layout'])) {
            $this->renderLayout($res['parent_layout'], array_merge($vars, ['content' => $layout_content]));
        } else echo $layout_content;
    }
    //endregion

}