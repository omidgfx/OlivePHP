<?php namespace Olive\Debug;


use Throwable;

abstract class ExceptionHandler
{
    public static function catch(Throwable $throwable): void {
        //todo
        echo get_class($throwable) . ': ' . $throwable->getMessage();
    }
}