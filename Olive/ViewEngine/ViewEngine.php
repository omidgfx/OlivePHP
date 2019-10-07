<?php namespace Olive\ViewEngine;

use Olive\Exceptions\LayoutNotFoundException;
use Olive\Exceptions\ViewEngineException;
use Olive\Exceptions\ViewNotFoundException;

class ViewEngine
{
    /**
     * @param $view
     * @return string
     * @throws ViewEngineException
     * @throws ViewNotFoundException
     */
    public static function viewPath($view) {
        $path = "App/Views/$view.php";

        if (!file_exists($path))
            throw new ViewNotFoundException('View not found' . (DEBUG_MODE ? " '$path'" : null));

        if (!isSubdirOf('App/Views', $path))
            throw new ViewEngineException('Invalid view path'. (DEBUG_MODE ? " '$path'" : null));

        return $path;
    }

    /**
     * @param $layout
     * @return string
     * @throws ViewEngineException
     * @throws LayoutNotFoundException
     */
    public static function layoutPath($layout) {
        $path = "App/Layouts/$layout.php";

        if (!file_exists($path))
            throw new LayoutNotFoundException('Layout not found'. (DEBUG_MODE ? " '$path'" : null));

        if (!isSubdirOf('App/Layouts', $path))
            throw new ViewEngineException('Invalid layout path'. (DEBUG_MODE ? " '$path'" : null));

        return $path;
    }


    /**
     * @param string $view
     * @param array $variables associated array, keys are variables name and accessible in rendering view and layout
     * @throws ViewEngineException
     * @throws ViewNotFoundException
     * @throws LayoutNotFoundException
     */
    public static function render($view, $variables = []) {
        ob_start();

        $viewVariables = static::require(self::viewPath($view), $variables);

        $layout = $viewVariables['viewLayout'] ?? null;

        $viewContent = ob_get_clean();

        self::layout($layout, array_merge($variables, ['viewContent' => $viewContent]));
    }

    /**
     * @param string $path script php related path
     * @param array $variables
     * @return array defined variables in script
     */
    private static function require($path, $variables = []) {
        extract($variables, EXTR_OVERWRITE);
        /** @noinspection PhpIncludeInspection */
        require $path;
        return get_defined_vars();
    }

    /**
     * @param $layout
     * @param array $vars
     * @throws ViewEngineException
     * @throws LayoutNotFoundException
     */
    private static function layout($layout, $vars = []) {
        if ($layout === null) {
            echo $vars['viewContent'];
            return;
        }
        ob_start();
        $res            = self::require(self::layoutPath($layout), $vars);
        $layout_content = ob_get_clean();
        if (isset($res['viewParentLayout']))
            self::layout($res['viewParentLayout'], array_merge($vars, ['viewContent' => $layout_content]));
        else
            echo $layout_content;
    }
}
