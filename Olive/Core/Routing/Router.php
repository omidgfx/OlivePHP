<?php namespace Olive\Routing;

use Olive\Core;
use Olive\Exceptions\H404;
use Olive\manifest;


class Router {
    /** @var RouteBypass[] */
    private $bypasses = [];

    /** @var RouteMiddler[] */
    private $middlers = [];

    public function __construct() {

        # add route bypasses from the manifest
        # Find out the route and render the corresponding controller.
        manifest::routerBypasses($this);

        # Find route
        $route = $this->route();

        # Render Route
        Core::RenderRoute($route, $this->middlers);
    }


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

        $route = ltrim(urldecode(parse_url($_SERVER['PATH_INFO'] ?? '', PHP_URL_PATH)), '/');

        $route = rtrim($route, '');
        if($route === NULL)
            return $r;

        # Pass the route
        $route = $this->bypass($route);

        # Parse the route string
        $route = explode('/', $route);
        if(isset($route[0])) {
            while(TRUE) {
                $r->controller = $route[0];
                $r->action     = isset($route[1]) ? $route[1] : 'Index';
                $r->arguments  = array_slice($route, 2);
                if(Controller::exists($r->controller))
                    break;
                if(count($route) > 1)
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
        foreach($this->bypasses as &$bypass)
            if($m = $bypass->matchAndBypass($routeBypass)) {
                $routeBypass = $m;
                break;
            }


        return $routeBypass;
    }


}
