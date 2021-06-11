<?php namespace Olive\Contracts;

use manifest;
use Olive\Exceptions\LayoutNotFoundException;
use Olive\Exceptions\ViewEngineException;
use Olive\Exceptions\ViewNotFoundException;

class ViewEngine
{
    private static string|null $_rootPath = null;

    private static function rootPath(): string|null {
        if (self::$_rootPath) return self::$_rootPath;
        $root_path       = manifest::view_dir;
        $root_path       = trim($root_path, '/');
        self::$_rootPath = $root_path;
        return $root_path;
    }

    /**
     * @param $view
     * @return string
     * @throws ViewEngineException
     * @throws ViewNotFoundException
     */
    public static function getViewPath($view): string {
        $root_path = self::rootPath();
        $path      = "$root_path/$view.php";

        if (!file_exists($path))
            throw new ViewNotFoundException('View not found' . (DEBUG_MODE ? " '$path'" : null));

        if (!isSubDirectoryOf('App/Views', $path))
            throw new ViewEngineException('Invalid view path' . (DEBUG_MODE ? " '$path'" : null));

        return $path;
    }

    /**
     * @param $layout
     * @return string
     * @throws ViewEngineException
     * @throws LayoutNotFoundException
     */
    public static function getLayoutPath($layout): string {
        $root_path = self::rootPath() . '/layouts';

        $path = "$root_path/$layout.php";

        if (!file_exists($path))
            throw new LayoutNotFoundException('Layout not found' . (DEBUG_MODE ? " '$path'" : null));

        if (!isSubDirectoryOf($root_path, $path))
            throw new ViewEngineException('Invalid layout path' . (DEBUG_MODE ? " '$path'" : null));

        return $path;
    }


    /**
     * @param string $view
     * @param array $variables associated array, keys are variables name and accessible in rendering view and layout
     * @throws ViewEngineException
     * @throws ViewNotFoundException
     * @throws LayoutNotFoundException
     */
    public static function render(string $view, array $variables = []): void {
        ob_start();

        $viewVariables = static::require(self::getViewPath($view), $variables);

        $layout = $viewVariables['viewLayout'] ?? null;

        $viewContent = ob_get_clean();

        self::layout($layout, array_merge($variables, ['viewContent' => $viewContent]));
    }

    /**
     * @param string $path script php related path
     * @param array $variables
     * @return array defined variables in script
     */
    private static function require(string $path, array $variables = []): array {
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
    private static function layout($layout, array $vars = []): void {
        if ($layout === null) {
            echo $vars['viewContent'];
            return;
        }
        ob_start();
        $res            = self::require(self::getLayoutPath($layout), $vars);
        $layout_content = ob_get_clean();
        if (isset($res['viewParentLayout']))
            self::layout($res['viewParentLayout'], array_merge($vars, ['viewContent' => $layout_content]));
        else
            echo $layout_content;
    }
}
