<?php namespace Olive\Http\Results;

use Olive\Http\Response;

abstract class ActionResult extends Response
{
    abstract public function executeResult(): void;
}