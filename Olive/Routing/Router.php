<?php namespace Olive\Routing;

use Olive\Exceptions\H404;
use Olive\Exceptions\H501;
use Olive\manifest;


class Router
{

    //region Fields

    /** @var RouteBypass[] */
    private $bypasses = [];

    /** @var RouteMiddler[] */
    private $middlers = [];

    //endregion

    //region Constructors

    public function __construct() {

        # add route bypasses from the manifest
        # Find out the route and render the corresponding controller.
        manifest::routerMapping($this);

        # Find route
        $route = $this->route();

        # Render Route
        self::RenderRoute($route, $this->middlers);
    }

    //endregion

    //region Routing

    public function addBypass(RouteBypass $bypass) {
        $this->bypasses[] = $bypass;
    }

    public function addMiddler(RouteMiddler $middler) {
        $this->middlers[] = $middler;
    }

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
                $r->action     = isset($route[1]) ? $route[1] : 'Index';
                $r->arguments  = array_slice($route, 2);
                if (Controller::exists($r->controller))
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
     * @return RouteBypass
     */
    public function bypass($routeBypass) {
        foreach ($this->bypasses as &$bypass)
            if ($m = $bypass->matchAndBypass($routeBypass)) {
                $routeBypass = $m;
                break;
            }


        return $routeBypass;
    }

    //endregion

    //region Renderers

    /**
     * @param Route $route
     * @param RouteMiddler[] $middlers
     * @throws H404
     * @throws H501
     *
     */
    private static function RenderRoute(Route $route, $middlers = []) {
        try {
            $next = true;
            /** @var RouteMiddler $middler */
            foreach ($middlers as $middler) {
                $next = self::RenderMiddleware($middler, $route);
                if (!$next) break;
            }
            //Render the controller
            if ($next)
                self::RenderController($route->controller, $route->action, $route->arguments, $route);
        } catch (\Exception $e) {
            //Handle the exception
            $route->arguments['exception'] = $e;
            self::RenderController('_error', null, $route->arguments, $route);
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
    private static function RenderController($name, $action = null, $params = [], &$route = null) {
        # Include controller file
        $path = Controller::getPath($name);
        if (!Controller::exists($name))
            throw new H404(DEBUG_MODE ? "Controller not found: $path" : 'Page not found.');

        $cn = "\\App\\Controllers\\" . str_replace('/', '\\', $name);
        if (!class_exists($cn))
            throw new H501('Wrong namespace' . (DEBUG_MODE ? ", Controller class in `$path` must be under `\\App\\Controllers\\{Path}` namespace" : null));
        $ctrl = new $cn($route);

        if (!$ctrl instanceof Controller)
            throw new H501('Controller class' . (DEBUG_MODE ? " in `$path`" : null) . ' is not an instace of Olive\Routing\Controller');

        # Validate action
        if ($action === null)
            # Unspecified action
            $action = 'Index';
        elseif (!method_exists($ctrl, "fn$action")) {
            # Method not exists
            # Use Index method
            $params = array_merge([$action], $params);
            $action = 'Index';
        }

        # Handle request to the controller
        $action = "fn$action";

        $parEnc = [];
        foreach ($params as $p => $k)
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
    private static function RenderMiddleware(RouteMiddler $routeMiddler, Route $route) {
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
            throw new H501('Middleware class' . (DEBUG_MODE ? " in `$path`" : null) . ' is not an instace of Olive\Routing\Middleware');


        # Handle request to the MiddleWare

        $argEnc = [];
        foreach ($route->arguments as $p => $k)
            $argEnc[$p] = is_string($k) ? urlencode($k) : $k;
        return $mdlr->perform($route, $argEnc);

    }

    //endregion

}
