<?php namespace Olive\Debug;

use Olive\Exceptions\ErrorException;

abstract class ErrorHandler
{
    /**
     * @throws ErrorException
     */
    public static function handler($code, $message, $file, $line, $context = null): void {
        throw ErrorException::makeByError($code, $message, $file, $line, $context);
    }
}