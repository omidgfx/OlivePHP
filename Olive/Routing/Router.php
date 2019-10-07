<?php namespace Olive\Routing;

use Exception;
use Olive\Exceptions\H404;
use Olive\Exceptions\H501;
use Olive\manifest;
use Olive\MVC\ActionResult;
use Olive\MVC\Controller;
use Olive\MVC\ControllerHelper;
use Olive\MVC\Middleware;
use ReflectionMethod;


class Router
{

    #region Fields

    /** @var RouteBypass[] */
    private $bypasses = [];

    /** @var RouteMiddler[] */
    private $middlers = [];

    #endregion

    #region Constructors

    public function __construct() {

        # add route bypasses from the manifest
        # Find out the route and render the corresponding controller.
        manifest::routerMapping($this);

        # Find route
        $route = $this->route();

        # Render Route
        self::FollowRoute($route, $this->middlers);
    }

    #endregion

    #region Routing

    /**
     * @return Route
     */
    public function route() {
        # Validate route
        $r                         = new Route;
        $r->controller             = '_error';
        $r->arguments['exception'] = new H404('ROUTERING FAILED.');

        $route = trim(urldecode(parse_url($_SERVER['PATH_INFO'] ?? '', PHP_URL_PATH)), '/');

        # Pass the route
        $route = $this->bypass($route);

        # Parse the route string
        $route = explode('/', $route);
        if (isset($route[0])) {
            while (true) {
                $r->controller = $route[0];
                $r->action     = $route[1] ?? 'Index';
                $r->arguments  = array_slice($route, 2);
                if (ControllerHelper::exists($r->controller))
                    break;
                if (count($route) > 1)
                    $route = array_merge([$route[0] . '/' . $route[1]], array_slice($route, 2));
                else break;
            }
        }

        return $r;
    }

    /**
     * @param string $routeBypass
     *
     * @return string
     */
    public function bypass($routeBypass) {
        foreach ($this->bypasses as &$bypass) {
            $m = $bypass->matchAndBypass($routeBypass);
            if ($m && is_string($m)) {
                $routeBypass = $m;
                break;
            }
        }

        return $routeBypass;
    }

    /**
     * @param Route $route
     * @param RouteMiddler[] $middlers
     */
    private static function FollowRoute(Route $route, $middlers = []) {
        try {
            $next = true;
            /** @var RouteMiddler $middler */
            foreach ($middlers as $middler) {
                $next = self::ExecuteMiddleware($middler, $route);
                if (!$next) break;
            }
            //Render the controller
            if ($next)
                self::HandleController($route->controller, $route);
        } catch (Exception $e) {
            //Handle the exception
            $route->arguments['exception'] = $e;
            try {
                self::HandleController('_error', $route, true);
            } catch (Exception $exception) {
                die ($exception->getMessage());
            }
        }
    }

    /**
     * @param RouteMiddler $routeMiddler
     * @param Route $route
     * @return bool
     * @throws H404
     * @throws H501
     */
    private static function ExecuteMiddleware(RouteMiddler $routeMiddler, Route $route) {
        # Include MiddleWare file
        $path = Middleware::getPath($routeMiddler->name);
        if (!file_exists($path))
            throw new H404(DEBUG_MODE ? "Middleware not found: $path" : 'Page not found.');

        $cn = "App\\Middlewares\\$routeMiddler->name";
        if (!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Middler class in `$path` must be under `\\App\\Middlewares\\{Path}` namespace" : null));

        # Create a new instance of the MiddleWare handler
        /** @var Middleware $mdlr */
        $mdlr = new $cn;

        if (!$mdlr instanceof Middleware)
            throw new H501('Middleware class' . (DEBUG_MODE ? " in `$path`" : null) . ' is not an instace of Olive\MVC\Middleware');


        # Handle request to the MiddleWare

        $argEnc = [];
        foreach ($route->arguments as $p => $k)
            $argEnc[$p] = is_string($k) ? urlencode($k) : $k;
        return $mdlr->execute($route, $argEnc);

    }

    #endregion

    #region Renderers

    /**
     * @param $name
     * @param Route $route
     * @param bool $actinIndexOnly
     * @throws H404
     * @throws H501
     */
    private static function HandleController($name, Route $route, $actinIndexOnly = false) {
        # Include controller file
        $path = ControllerHelper::getPath($name);
        if (!ControllerHelper::exists($name))
            throw new H404(DEBUG_MODE ? "Controller not found: $path" : 'Page not found.');

        $cn = "\\App\\Controllers\\" . str_replace('/', '\\', $name);
        if (!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Controller class in `$path` must be under `\\App\\Controllers\\{Path}` namespace" : null));

        /** @var Controller $controller */
        $controller = new $cn($route);

        if (!$controller instanceof Controller)
            throw new H501('Controller class' . (DEBUG_MODE ? " in `$path`" : null) . ' is not an instace of Olive\MVC\Controller');

        $action = $actinIndexOnly === false ? $route->action : null;
        $args   = $route->arguments;

        # Validate action
        if ($action === null)
            $action = 'Index';
        # Unspecified action
        elseif (!method_exists($controller, $action)) {
            # Method not exists
            # Use Index method
            $args   = array_merge([$action], $args);
            $action = 'Index';
        }

        # Do some check for method visibility
        try {
            $rfl = new ReflectionMethod($cn, $action);
            if (!$rfl->isPublic() || $rfl->isFinal() || $rfl->isStatic() || $rfl->isConstructor())
                throw new H404('');
            unset($rfl);
        } catch (Exception $exception) {
            throw new H404('Controller action ' . (DEBUG_MODE ? "`$action` in `$path`" : null)
                . ' is not public or is static or final or is a class constructor');
        }

        # Handle request to the controller
        $argsEncoded = [];
        foreach ($args as $p => $k)
            $argsEncoded[$p] = is_string($k) ? urlencode($k) : $k;

        $route->action    = $action;
        $route->arguments = $args;

        # Run controller action
        $actionResult = $controller->$action($argsEncoded);

        if ($actionResult && $actionResult instanceof ActionResult)
            $actionResult->executeResult($controller);

    }

    public function addBypass(RouteBypass $bypass) {
        $this->bypasses[] = $bypass;
    }

    public function addMiddler(RouteMiddler $middler) {
        $this->middlers[] = $middler;
    }

    #endregion

}
