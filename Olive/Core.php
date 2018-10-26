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
     * @uses Core::requireModule()
     * @see  Core::requireModule()
     * @throws OliveFatalError
     */
    public static function requireModules(array $modules) {
        foreach($modules as $module)
            self::requireModule($module);
    }


    /**
     * ##RequireModule
     * Boot, Require and start modules
     *
     * ###Perform scenario (priority):
     * > 1. <font color="orange">`Olive/Support/`</font><b color="lime">`$module`</b><font color="orange">`.php`</font>
     * > 2. <font color="orange">`Olive/Support/`</font><b color="lime">`$module`</b><font color="orange">`/loader.php`</font>
     * > 3. {@see Core::boot Boot}s module directory from<br>
     *    <font color="orange">`Olive/Support/`</font><b color="lime">`$module`</b><font color="orange">`/`</font><br><br>
     * > 4. <font color="#ff8888">`App/Modules/`</font><b color="lime">`$module`</b><font color="#ff8888">`.php`</font>
     * > 5. <font color="#ff8888">`App/Modules/`</font><b color="lime">`$module`</b><font color="#ff8888">`/loader.php`</font>
     * > 6. {@see Core::boot Boot}s module directory from<br>
     *    <font color="#ff8888">`App/Modules/`</font><b color="lime">`$module`</b><font color="#ff8888">`/`</font>
     *
     *
     *
     * @param string $module
     * @throws OliveFatalError
     */
    public static function requireModule($module) {
        $places = ['Olive/Support', 'App/Modules'];

        foreach($places as $place) {
            if(file_exists($path = "$place/$module.php")) {
                /** @noinspection PhpIncludeInspection */
                require_once $path;
                return;
            }
            if(is_dir("$place/$module")) {
                if(file_exists($path = "$place/$module/loader.php")) {
                    /** @noinspection PhpIncludeInspection */
                    require_once $path;
                    return;
                }
                static::boot("$place/$module");
                return;
            }
        }
        throw new OliveFatalError("Module not found '$module'");
    }

    /**
     * @param string $view_name
     * @param array $params associated array, keys are variables name and accessible in rendering view and layout
     * @param string $layout name of layout, by passing null, uses $layout in view
     * @throws H404
     */
    public static function renderView($view_name, $params = [], $layout = NULL) {
        ob_start();

        $vars = self::requireScript("App/Views/$view_name.php", $params);

        if(!isset($vars['layout']))
            $vars['layout'] = $layout;
        elseif(!$layout)
            $layout = $vars['layout'];

        $viewContent = ob_get_clean();

        self::renderLayout($layout, array_merge($params, ['content' => $viewContent]));
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


        $cn = "\\App\\Controllers\\$name";
        if(!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Controller class in `$path` must be under `\\App\\Controllers` namespace" : NULL));
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


        $cn = "App\\Middlewares\\$routeMiddler->name";
        if(!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Middler class in `$path` must be under `\\App\\Middlewares` namespace" : NULL));

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
        $res            = self::requireScript("App/Layouts/$layout.php", $vars);
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

    /**
     * ##Boot given directory
     * Requires all php files in directory (recursively)
     * <div style="color:orange;padding-top:0">
     * * Sub-directories are first priority to boot
     * * Underscore prefix (_) has most priority in require
     * </font>
     *
     * @param $path
     */
    public static function boot($path) {

        if(is_null($path)) return;

        # read files and folders
        $list = glob("$path/*");
        if(count($list) == 0)
            return;
        $dirs = $files = [];

        foreach($list as $item) {
            if(is_dir($item))
                $dirs[] = $item;
            elseif(Text::endsWith('.php', $item, FALSE))
                $files[] = $item;
        }
        unset($list);

        # sort
        $sorter = function($a, $b) { return strcmp(str_replace('_', 0, $a), str_replace('_', 0, $b)); };
        usort($dirs, $sorter);
        usort($files, $sorter);

        # recursive call boot for folders
        foreach($dirs as $dir)
            self::boot($dir);

        # require files
        foreach($files as $item)
            /** @noinspection PhpIncludeInspection */
            require_once $item;
    }

    public static function loadBootables($path) {
        # read files and folders
        $list = glob("$path/*.boot");
        if(count($list) == 0)
            return;
        # boot
        foreach($list as $item) if(is_dir($item))
            self::boot($item);

    }
}
