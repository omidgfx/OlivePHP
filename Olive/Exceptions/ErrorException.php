<?php namespace Olive\Exceptions;


class ErrorException extends OliveException
{
    public mixed $context;

    public static function makeByError($code, $message, $file, $line, $context = null): static {
        $e          = new static;
        $e->code    = $code;
        $e->message = $message;
        $e->file    = $file;
        $e->line    = $line;
        $e->context = $context;
        return $e;
    }
}