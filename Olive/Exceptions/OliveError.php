<?php namespace Olive\Exceptions;

class OliveError extends OliveException
{
    public $code, $file, $line, $message;
}
