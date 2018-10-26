<?php namespace App\Routing;

use Olive\Routing\Middleware;
use Olive\Routing\Route;

class mi extends Middleware {

    /**
     * @param Route $route
     * @param array $args
     * @return bool
     */
    public function perform(Route $route, $args = []) {
        return TRUE;
    }
}