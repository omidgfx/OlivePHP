<?php namespace Olive\Routing;


class Handler
{
    public function __construct(public $callable, public $middlewares = []) {
    }
}