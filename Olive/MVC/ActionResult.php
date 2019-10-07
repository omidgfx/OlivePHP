<?php namespace Olive\MVC;

abstract class ActionResult extends ResponseResultBase
{
    abstract public function executeResult(Controller $controller);
}
