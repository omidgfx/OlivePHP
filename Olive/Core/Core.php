<?php

namespace Olive;

use Olive\Exceptions\H404;
use Olive\Exceptions\H501;
use Olive\Exceptions\OliveError;
use Olive\Exceptions\OliveFatalError;
use Olive\Routing\Controller;
use Olive\Routing\Middleware;
use Olive\Routing\Route;
use Olive\Routing\RouteMiddler;
use Olive\Util\Text;

abstract class Core {
    /**
     * @param string[] $modules
     * @see Core::requireModule()
     * @throws OliveFatalError
     */
    public static function requireModules(array $modules) {
        foreach($modules as $module)
            self::requireModule($module);
    }


    /**
     * @param string $module use the name of php files/directories under olive/modules. if your module has multiple files, you can put them in a single folder together and create a _module.php for your module start handler
     * @throws OliveFatalError
     */
    public static function requireModule($module) {

        # External modules

        $_p = "Olive/Modules/$module";
        $_p .= is_dir($_p) ? "/loader.php" : '.php';

        if(!file_exists($_p)) {
            # Internal
            $_p = "Olive/Core/Support/$module";
            $_p .= is_dir($_p) ? "/loader.php" : '.php';

            if(!file_exists($_p))
                throw new OliveFatalError("Module not found '$_p'");
        }

        /** @noinspection PhpIncludeInspection */
        require_once $_p;

    }

    /**
     * @param string $view_name
     * @param array $params associated array, keys are variables name and accessible in rendering view and layout
     * @param string $layout name of layout, by passing null, uses $layout in view
     * @throws H404
     */
    public static function renderView($view_name, $params = [], $layout = NULL) {
        ob_start();

        $vars = self::requireScript("Olive/Views/$view_name.php", $params);

        if(!isset($vars['layout']))
            $vars['layout'] = $layout;
        elseif(!$layout)
            $layout = $vars['layout'];

        $viewContent = ob_get_clean();

        self::renderLayout($layout, array_merge($params, ['content' => $viewContent]));
    }

    /**
     * @param string $widget
     * @param array $vars associated array, keys are variables name and accessible in rendering widget
     * @throws H404
     */
    public static function renderWidget($widget, $vars = []) {
        self::requireScript("Olive/Widgets/$widget.php", $vars);
    }

    /**
     * @param Route $route
     * @param RouteMiddler[] $middlers
     * @throws H404
     * @throws H501
     *
     */
    public static function RenderRoute(Route $route, $middlers = []) {
        try {
            $next = TRUE;
            /** @var RouteMiddler $middler */
            foreach($middlers as $middler) {
                $next = self::RenderMiddleware($middler, $route);
                if(!$next) break;
            }
            //Render the controller
            if($next)
                self::RenderController($route->controller, $route->action, $route->arguments, $route);
        } catch(\Exception $e) {
            //Handle the exception
            $route->arguments['exception'] = $e;
            self::RenderController('_error', NULL, $route->arguments, $route);
        }
    }

    /**
     * @param $name
     * @param null $action
     * @param array $params
     * @param null $route
     * @throws H404
     * @throws H501
     */
    public static function RenderController($name, $action = NULL, $params = [], &$route = NULL) {
        # Include controller file
        $path = Controller::getPath($name);
        if(!Controller::exists($name))
            throw new H404(DEBUG_MODE ? "Controller not found: $path" : 'Page not found.');

        /** @noinspection PhpIncludeInspection */
        require_once $path;


        $cn = "\\Olive\\Routing\\$name";
        if(!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Controller class in `$path` must be under `\\Olive\\Routing` namespace" : NULL));
        $ctrl = new $cn($route);

        if(!$ctrl instanceof Controller)
            throw new H501('Controller class' . (DEBUG_MODE ? " in `$path`" : NULL) . ' is not an instace of Olive\Routing\Controller');

        # Validate action
        if($action === NULL)
            # Unspecified action
            $action = 'Index';
        elseif(!method_exists($ctrl, "fn$action")) {
            # Method not exists
            # Use Index method
            $params = array_merge([$action], $params);
            $action = 'Index';
        }

        # Handle request to the controller
        $action = "fn$action";

        $parEnc = [];
        foreach($params as $p => $k)
            $parEnc[$p] = is_string($k) ? urlencode($k) : $k;
        $ctrl->$action($parEnc);

    }

    /**
     * @param RouteMiddler $routeMiddler
     * @param Route $route
     * @return bool
     * @throws H404
     * @throws H501
     */
    public static function RenderMiddleware(RouteMiddler $routeMiddler, Route $route) {
        # Include MiddleWare file
        $path = Middleware::getPath($routeMiddler->name);
        if(!file_exists($path))
            throw new H404(DEBUG_MODE ? "Middleware not found: $path" : 'Page not found.');
        /** @noinspection PhpIncludeInspection */
        require_once $path;


        $cn = "Olive\\Routing\\$routeMiddler->name";
        if(!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Middler class in `$path` must be under `\\Olive\\Routing` namespace" : NULL));

        # Create a new instance of the MiddleWare handler
        /** @var Middleware $mdlr */
        $mdlr = new $cn;

        if(!$mdlr instanceof Middleware)
            throw new H501('Middleware class' . (DEBUG_MODE ? " in `$path`" : NULL) . ' is not an instace of Olive\Routing\Middleware');


        # Handle request to the MiddleWare

        $argEnc = [];
        foreach($route->arguments as $p => $k)
            $argEnc[$p] = is_string($k) ? urlencode($k) : $k;
        return $mdlr->perform($route, $argEnc);

    }

    /**
     * @param string $target_url
     */
    public static function redirect($target_url) {
        self::setHeader('Location', $target_url);
        die;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @param null|int $response_code look at (<a href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes" target="_blank">List_of_HTTP_status_codes</a>) for more information.
     */
    public static function setHeader($name, $value, $replace = TRUE, $response_code = NULL) {
        if($value)
            $s = "$name: $value";
        else
            $s = $name;
        header($s, $replace, $response_code);
    }

    /**
     * @param int $response_code look at (<a href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes" target="_blank">List_of_HTTP_status_codes</a>) for more information.
     */
    public static function setHttpResponseCode($response_code) {
        http_response_code($response_code);
    }

    /**
     * @param string $path script php related path
     * @param array $variables
     * @return array defined variables in script
     * @throws H404
     */
    public static function requireScript($path, $variables = []) {
        if(!file_exists($path))
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
    private static function renderLayout($layout, $vars = []) {
        if($layout == NULL) {
            echo $vars['content'];

            return;
        }

        ob_start();
        $res            = self::requireScript("Olive/Layouts/$layout.php", $vars);
        $layout_content = ob_get_clean();
        if(isset($res['parent_layout'])) {
            self::renderLayout($res['parent_layout'], array_merge($vars, ['content' => $layout_content]));
        } else echo $layout_content;
    }

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param null $errcontext
     * @throws OliveError
     */
    public final static function errorHandler($code, $message, $file, $line, $errcontext = NULL) {
        $e              = new OliveError;
        $e->code        = $code;
        $e->message     = $message;
        $e->file        = $file;
        $e->line        = $line;
        $e->{'context'} = $errcontext;
        throw $e;
    }

    public final static function shutdownHandler() {
        if(($error = error_get_last()) !== NULL) {
            $exception          = new OliveFatalError;
            $exception->code    = $error["type"];
            $exception->message = $error["message"];
            $exception->file    = $error["file"];
            $exception->line    = $error["line"];

            if(DEBUG_MODE) {
                echo "<div style='background:#fafafa;color:#777;margin: 10px;border: 1px solid #ddd;padding: 10px;border-radius: 8px'><h1><span style='color:red'>Fatal error captured:</span></h1>",
                "<pre>";
                print_r($error);
                echo "</pre></div>";
            } else {
                ob_clean();
                http_response_code(500);
                echo "<h1 style='color:red;text-align:center;'>FATAL ERROR</h1>";
            }
        }

    }

    public static function boot($path = NULL) {
        if($path == NULL) $path = 'Olive/Autoloads';
        $list = glob("$path/*");
        usort($list, function($a, $b) {
            return strcmp(str_replace('_', 0, $a), str_replace('_', 0, $b));
        });
        foreach($list as $item)
            if(is_dir($item))
                self::boot($item);
            elseif(Text::endsWith('.php', $item, TRUE))
                /** @noinspection PhpIncludeInspection */
                require_once $item;
    }
}
