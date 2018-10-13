<?php namespace Olive\Routing;

use Olive\Core;
use Olive\Exceptions\H404;
use Olive\Http\req;
use Olive\manifest;

define('CONTROLLER_INDEX', 'Index');
define('ROUTE_KEY', '___ROUTE');

class Router {
    /** @var RouteBypass[] */
    private $bypasses = [];

    /** @var RouteMiddler[] */
    private $middlers = [];

    public function __construct() {
        // add route bypasses from the manifest
        // Find out the route and render the corresponding controller.
        manifest::routerBypasses($this);


        // Find route
        $route = $this->route();

        // Render Route
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
        //Validate route
        $r                         = new Route;
        $r->controller             = '_error';
        $r->arguments['exception'] = new H404('ROUTERING FAILED.');

        $route = req::get(ROUTE_KEY, '');
        if($route === NULL)
            return $r;

        unset($_GET[ROUTE_KEY]);
        unset($_REQUEST[ROUTE_KEY]);


        $queryString = explode('&', $_SERVER['QUERY_STRING']);
        $qsClear     = [];
        foreach($queryString as $qs)
            if(substr($qs, 0, strlen(ROUTE_KEY) + 1) != (ROUTE_KEY . '='))
                $qsClear[] = $qs;

        $r->url = rtrim($r->url, '/');


        //Pass the route
        $route = $this->bypass($route);

        //Parse the route string
        $route = explode('/', $route);
        if(isset($route[0])) {
            while(TRUE) {
                $r->controller = $route[0];
                $r->action     = isset($route[1]) ? $route[1] : CONTROLLER_INDEX;
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
