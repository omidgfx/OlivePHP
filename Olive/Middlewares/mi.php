<?php namespace Olive\Routing;

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